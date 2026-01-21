<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Algolia\Business\Searcher;

use Algolia\AlgoliaSearch\SearchClient;
use ArrayObject;
use Exception;
use Generated\Shared\Transfer\AlgoliaConfigTransfer;
use Generated\Shared\Transfer\AlgoliaSearchResponseTransfer;
use Generated\Shared\Transfer\SearchRequestTransfer;
use Generated\Shared\Transfer\SuggestionsSearchResponseTransfer;
use Spryker\Shared\Log\LoggerTrait;
use SprykerEco\Shared\Algolia\Enum\AlgoliaCmsPageObjectEnum;
use SprykerEco\Shared\Algolia\Enum\AlgoliaEntityNameEnum;
use SprykerEco\Zed\Algolia\AlgoliaConfig;
use SprykerEco\Zed\Algolia\Business\Api\Creator\SearchClientCreatorInterface;
use SprykerEco\Zed\Algolia\Business\Api\Exception\AlgoliaConfigNotFoundException;
use SprykerEco\Zed\Algolia\Business\Api\Response\Builder\SuggestionsSearchResponseBuilderInterface;
use SprykerEco\Zed\Algolia\Business\IndexResolver\IndexNameResolverInterface;
use SprykerEco\Zed\Algolia\Business\Resolver\AlgoliaConfigResolverInterface;
use Throwable;

class SuggestionsSearcher implements SuggestionsSearcherInterface
{
    use LoggerTrait;

    public function __construct(
        protected IndexNameResolverInterface $indexNameResolver,
        protected SearchClientCreatorInterface $searchClientCreator,
        protected SuggestionsSearchResponseBuilderInterface $suggestionsSearchResponseBuilder,
        protected AlgoliaConfig $algoliaConfig,
        protected AlgoliaConfigResolverInterface $algoliaConfigResolver
    ) {
    }

    public function searchSuggestions(SearchRequestTransfer $searchRequestTransfer): SuggestionsSearchResponseTransfer
    {
        $result = [
            'suggestions' => [],
            'completions' => [],
            'categories' => [],
        ];

        $algoliaConfigTransfer = $this->algoliaConfigResolver->findConfig();
        if ($algoliaConfigTransfer === null) {
            throw new AlgoliaConfigNotFoundException('Algolia configuration not found.');
        }

        $searchClient = $this->searchClientCreator->createSearchClientFromConfig($algoliaConfigTransfer, true);

        if ($algoliaConfigTransfer->getIsSearchInFrontendEnabledForProducts()) {
            $result = $this->getProductsResult($searchRequestTransfer, $searchClient, $algoliaConfigTransfer, $result);
        }

        $result = $this->expandResultUsingAdditionalIndexes($searchClient, $searchRequestTransfer, $algoliaConfigTransfer, $result);

        $algoliaResponseTransfer = (new AlgoliaSearchResponseTransfer())
            ->setSearchResults($result)
            ->setIsSuccessful(true);

        return $this->suggestionsSearchResponseBuilder->buildSuccessfulResponse($algoliaResponseTransfer, $searchRequestTransfer);
    }

    protected function logUnexpectedThrowable(Throwable $throwable, SearchRequestTransfer $searchRequestTransfer): void
    {
        $this->getLogger()->error(
            'Algolia suggestions request has failed. Please check the module configuration or index name in the Algolia account.',
            [
                'searchRequest' => $searchRequestTransfer,
                'exception' => $throwable,
            ],
        );
    }

    /**
     * @return array
     */
    protected function expandResultUsingAdditionalIndexes(
        SearchClient $searchClient,
        SearchRequestTransfer $searchRequestTransfer,
        AlgoliaConfigTransfer $algoliaConfigTransfer,
        array $result
    ): array {
        $indexes = $this->getAdditionalIndexes($searchRequestTransfer, $algoliaConfigTransfer);

        $personalizationParameters = [];
        if ($searchRequestTransfer->getUserToken()) {
            $personalizationParameters = [
                'userToken' => $searchRequestTransfer->getUserTokenOrFail(),
            ];
        }

        foreach ($indexes as $index => $indexName) {
            $searchParameters = [
                'hitsPerPage' => 10,
                ...$personalizationParameters,
            ];

            if (str_contains($indexName, AlgoliaEntityNameEnum::CMS_PAGE->value)) {
                // add additional params and filter for CMS pages
                $searchParameters['attributesToSnippet'] = 'content:50';
                $searchParameters['snippetEllipsisText'] = '...';
                $searchParameters['attributesToHighlight'] = [];
                $searchParameters['filters'] = sprintf('%s:%s', AlgoliaCmsPageObjectEnum::STORE->value, $searchRequestTransfer->getStoreNameOrFail());
            }

            $result[$index] = [];

            try {
                $indexResult = $searchClient
                    ->initIndex($indexName)
                    ->search(
                        $searchRequestTransfer->getQuery() ?? '',
                        $searchParameters,
                    );

                $result[$index] = $indexResult;
            } catch (Throwable $throwable) {
                $this->logUnexpectedThrowable($throwable, $searchRequestTransfer);
            }
        }

        return $result;
    }

    /**
     * @return array<string, string>
     */
    protected function getAdditionalIndexes(SearchRequestTransfer $searchRequestTransfer, AlgoliaConfigTransfer $algoliaConfigTransfer): array
    {
        $indexes = [];

        if ($algoliaConfigTransfer->getIsSearchInFrontendEnabledForCmsPages()) {
            $indexes[AlgoliaEntityNameEnum::CMS_PAGE->value] = $this->indexNameResolver->resolveCmsPageIndexName($searchRequestTransfer->getLocaleOrFail(), $algoliaConfigTransfer->getTenantIdentifierOrFail());
        }

        if ($algoliaConfigTransfer->getIsIndexMappingEnabled()) {
            foreach ($this->getEntitiesFromIndexMappings($algoliaConfigTransfer->getEntityToIndexMappings()) as $sourceIdentifier) {
                if ($sourceIdentifier === AlgoliaEntityNameEnum::PRODUCT->value) {
                    continue;
                }

                try {
                    $indexes[$sourceIdentifier] = $this->indexNameResolver->resolveIndexNameByMapping(
                        $sourceIdentifier,
                        $searchRequestTransfer->getStoreNameOrFail(),
                        $searchRequestTransfer->getLocaleOrFail(),
                        $algoliaConfigTransfer->getEntityToIndexMappings(),
                    );
                } catch (Exception $e) {
                }
            }
        }

        return $indexes;
    }

    /**
     * @param \ArrayObject<\Generated\Shared\Transfer\EntityToIndexMappingTransfer> $entityToIndexMappings
     *
     * @return list<string>
     */
    protected function getEntitiesFromIndexMappings(ArrayObject $entityToIndexMappings): array
    {
        $entities = [];
        foreach ($entityToIndexMappings as $entityToIndexMappingTransfer) {
            $entities[] = $entityToIndexMappingTransfer->getSourceIdentifier();
        }

        return $entities;
    }

    /**
     * @return array
     */
    protected function getProductsResult(
        SearchRequestTransfer $searchRequestTransfer,
        SearchClient $searchClient,
        AlgoliaConfigTransfer $algoliaConfigTransfer,
        array $result
    ): array {
        $productIndexName = null;

        if ($algoliaConfigTransfer->getIsIndexMappingEnabled()) {
            try {
                $productIndexName = $this->indexNameResolver->resolveIndexNameByMapping(
                    AlgoliaEntityNameEnum::PRODUCT->value,
                    $searchRequestTransfer->getStoreNameOrFail(),
                    $searchRequestTransfer->getLocaleOrFail(),
                    $algoliaConfigTransfer->getEntityToIndexMappings(),
                );
            } catch (Exception $e) {
            }
        }

        if ($productIndexName === null) {
            $productIndexName = $this->indexNameResolver->resolveProductIndexName(
                $algoliaConfigTransfer->getTenantIdentifierOrFail(),
                $searchRequestTransfer->getStoreName(),
                $searchRequestTransfer->getLocale(),
            );
        }

        $suggestionIndexName = $this->indexNameResolver->resolveProductsSuggestionIndexNameFromProductIndexName($productIndexName);

        $personalizationParameters = [];
        if ($searchRequestTransfer->getUserToken()) {
            $personalizationParameters = [
                'userToken' => $searchRequestTransfer->getUserTokenOrFail(),
            ];
        }

        try {
            $completions = $searchClient
                ->initIndex($suggestionIndexName)
                ->search(
                    $searchRequestTransfer->getQuery() ?? '',
                    ['hitsPerPage' => 10, ...$personalizationParameters],
                );

            $result['completions'] = $completions;
        } catch (Throwable $throwable) {
            $this->logUnexpectedThrowable($throwable, $searchRequestTransfer);
        }

        try {
            $suggestions = $searchClient
                ->initIndex($productIndexName)
                ->search(
                    $searchRequestTransfer->getQuery() ?? '',
                    [
                        'hitsPerPage' => 10,
                        'attributesToHighlight' => $this->algoliaConfig->getAttributesToHighlight(),
                        ...$personalizationParameters,
                    ],
                );

            $result['suggestions'] = $suggestions;
        } catch (Throwable $throwable) {
            $this->logUnexpectedThrowable($throwable, $searchRequestTransfer);
        }

        try {
            $categories = $searchClient
                ->initIndex($productIndexName)
                ->searchForFacetValues(
                    $this->algoliaConfig::INDEXED_PRODUCT_FIELD_NAME_CATEGORY,
                    $searchRequestTransfer->getQuery() ?? '',
                    [
                        'hitsPerPage' => 10,
                        'X-Forwarded-For' => $searchRequestTransfer->getUserIp() ?? null,
                        ...$personalizationParameters,
                    ],
                );

            $result['categories'] = $categories;
        } catch (Throwable $throwable) {
            $this->logUnexpectedThrowable($throwable, $searchRequestTransfer);
        }

        return $result;
    }
}
