<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Algolia\Business\Searcher;

use Algolia\AlgoliaSearch\Exceptions\NotFoundException;
use Generated\Shared\Transfer\AlgoliaConfigTransfer;
use Generated\Shared\Transfer\SearchRequestTransfer;
use Generated\Shared\Transfer\SearchResponseTransfer;
use Spryker\Shared\Log\LoggerTrait;
use SprykerEco\Shared\Algolia\Enum\AlgoliaCmsPageObjectEnum;
use SprykerEco\Shared\Algolia\Enum\AlgoliaEntityNameEnum;
use SprykerEco\Zed\Algolia\Business\Api\Client\SearchIndexClientInterface;
use SprykerEco\Zed\Algolia\Business\Api\Creator\SearchClientCreatorInterface;
use SprykerEco\Zed\Algolia\Business\Api\Response\Builder\SearchResponseBuilderInterface;
use SprykerEco\Zed\Algolia\Business\Api\SearchParameters\SearchParametersResolverInterface;
use SprykerEco\Zed\Algolia\Business\IndexResolver\SearchIndexResolverInterface;
use SprykerEco\Zed\Algolia\Business\Resolver\AlgoliaConfigResolverInterface;
use Throwable;

class Searcher implements SearcherInterface
{
    use LoggerTrait;

    /**
     * @var string
     */
    protected const ERROR_MESSAGE = 'The search request has failed. Please check an App configuration or index name in the Algolia account';

    /**
     * @var int
     */
    protected const ERROR_CODE = 424;

    public function __construct(
        protected SearchIndexResolverInterface $searchIndexResolver,
        protected SearchParametersResolverInterface $searchParametersResolver,
        protected SearchResponseBuilderInterface $searchResponseBuilder,
        protected SearchClientCreatorInterface $searchClientCreator,
        protected AlgoliaConfigResolverInterface $algoliaConfigResolver
    ) {
    }

    public function search(SearchRequestTransfer $searchRequestTransfer): SearchResponseTransfer
    {
        $algoliaConfigTransfer = $this->algoliaConfigResolver->getConfig();

        if ($searchRequestTransfer->getSourceIdentifier() === AlgoliaEntityNameEnum::PRODUCT->value) {
            return $this->searchProduct($searchRequestTransfer, $algoliaConfigTransfer);
        }

        if ($searchRequestTransfer->getSourceIdentifier() === AlgoliaEntityNameEnum::CMS_PAGE->value) {
            return $this->searchCmsPage($searchRequestTransfer, $algoliaConfigTransfer);
        }

        return $this->searchBySourceIdentifier($searchRequestTransfer, $algoliaConfigTransfer);
    }

    protected function searchProduct(SearchRequestTransfer $searchRequestTransfer, AlgoliaConfigTransfer $algoliaConfigTransfer): SearchResponseTransfer
    {
        try {
            $searchIndexClient = $this->searchIndexResolver->getSearchIndexClientForSearchRequest($searchRequestTransfer, $algoliaConfigTransfer);

            return $this->performSearch($searchRequestTransfer, $searchIndexClient, $algoliaConfigTransfer);
        } catch (NotFoundException $notFoundException) {
            if (!$searchRequestTransfer->getSort()) {
                $this->logUnexpectedThrowable($notFoundException, $searchRequestTransfer);

                // If sorting was not provided it means that primary index already bean asked.
                return $this->searchResponseBuilder->buildUnsuccessfulResponse(static::ERROR_MESSAGE, static::ERROR_CODE);
            }

            $searchIndexClient = $this->searchIndexResolver->getSearchIndexClientWithPrimarySearchIndex($searchRequestTransfer, $algoliaConfigTransfer);

            return $this->performSearch($searchRequestTransfer, $searchIndexClient, $algoliaConfigTransfer);
        } catch (Throwable $throwable) {
            $this->logUnexpectedThrowable($throwable, $searchRequestTransfer);

            throw $throwable;
        }
    }

    protected function searchCmsPage(SearchRequestTransfer $searchRequestTransfer, AlgoliaConfigTransfer $algoliaConfigTransfer): SearchResponseTransfer
    {
        try {
            $searchIndexClient = $this->searchIndexResolver->getSearchIndexClientForSearchRequest($searchRequestTransfer, $algoliaConfigTransfer);

            $searchResponseTransfer = $this->performSearch($searchRequestTransfer, $searchIndexClient, $algoliaConfigTransfer);

            // Remove store and validity dates facets from response, as it is not needed for CMS pages.
            $facets = $searchResponseTransfer->getFacets();
            unset(
                $facets[AlgoliaCmsPageObjectEnum::STORE->value],
                $facets[AlgoliaCmsPageObjectEnum::VALID_FROM->value],
                $facets[AlgoliaCmsPageObjectEnum::VALID_TO->value],
            );
            $searchResponseTransfer->setFacets($facets);

            return $searchResponseTransfer;
        } catch (Throwable $throwable) {
            $this->logUnexpectedThrowable($throwable, $searchRequestTransfer);

            throw $throwable;
        }
    }

    protected function searchBySourceIdentifier(
        SearchRequestTransfer $searchRequestTransfer,
        AlgoliaConfigTransfer $algoliaConfigTransfer
    ): SearchResponseTransfer {
        if (!$algoliaConfigTransfer->getIsIndexMappingEnabled()) {
            return $this->searchResponseBuilder->buildUnsuccessfulResponse(sprintf('Index mapping is not enabled. Please configure it for %s on the module configuration.', $searchRequestTransfer->getSourceIdentifier()), static::ERROR_CODE);
        }

        try {
            $searchIndexClient = $this->searchIndexResolver->getSearchIndexClientForSearchRequest($searchRequestTransfer, $algoliaConfigTransfer);

            return $this->performSearch($searchRequestTransfer, $searchIndexClient, $algoliaConfigTransfer);
        } catch (Throwable $throwable) {
            return $this->searchResponseBuilder->buildUnsuccessfulResponse($throwable->getMessage(), static::ERROR_CODE);
        }
    }

    protected function performSearch(
        SearchRequestTransfer $searchRequestTransfer,
        SearchIndexClientInterface $searchIndexClient,
        AlgoliaConfigTransfer $algoliaConfigTransfer
    ): SearchResponseTransfer {
        $searchParameters = $this->searchParametersResolver->getSearchParameters($searchRequestTransfer, $algoliaConfigTransfer);

        $algoliaResponseTransfer = $searchIndexClient->search($searchRequestTransfer->getQuery() ?? '', $searchParameters);

        return $this->searchResponseBuilder->buildSuccessfulResponse($algoliaResponseTransfer, $searchRequestTransfer);
    }

    protected function logUnexpectedThrowable(
        Throwable $throwable,
        SearchRequestTransfer $searchRequestTransfer
    ): void {
        $this->getLogger()->error(
            'Algolia Search request has failed.',
            [
                'searchRequest' => $searchRequestTransfer,
                'exception' => $throwable,
            ],
        );
    }
}
