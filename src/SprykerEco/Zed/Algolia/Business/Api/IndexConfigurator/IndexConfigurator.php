<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Api\IndexConfigurator;

use Algolia\AlgoliaSearch\Api\SearchClient;
use Algolia\AlgoliaSearch\Exceptions\AlgoliaException;
use Generated\Shared\Transfer\AlgoliaConfigTransfer;
use Generated\Shared\Transfer\IndexConfigurationResponseTransfer;
use Locale;
use Spryker\Shared\Log\LoggerTrait;
use SprykerEco\Zed\Algolia\AlgoliaConfig;
use SprykerEco\Zed\Algolia\Business\Handler\SuggestionIndexHandlerInterface;

class IndexConfigurator implements IndexConfiguratorInterface
{
    use LoggerTrait;

    protected const string ATTRIBUTE_NAME_PRODUCT_ABSTRACT_SKU = 'product_abstract_sku';

    protected const string ATTRIBUTE_NAME_RATING = 'rating';

    protected const string ATTRIBUTE_NAME_NAME = 'name';

    protected const string ATTRIBUTE_NAME_ABSTRACT_NAME = 'abstract_name';

    protected const string ATTRIBUTE_NAME_PRICES_EUR_GROSS = 'prices.eur.gross';

    protected const string ATTRIBUTE_NAME_PRICES_EUR_NET = 'prices.eur.net';

    protected const string ERROR_MESSAGE_TEMPLATE = 'Error happened while saving settings for index %s; error text: %s';

    public function __construct(protected SuggestionIndexHandlerInterface $suggestionIndexHandler, protected AlgoliaConfig $algoliaConfig)
    {
    }

    public function configureIndex(string $indexName, SearchClient $searchClient, string $locale, AlgoliaConfigTransfer $algoliaConfigTransfer): void
    {
        $replicaNamesWithRankingAttributes = $this->getReplicaNamesWithRankingAttributes($indexName, $algoliaConfigTransfer);
        $response = $searchClient->setSettings(
            $indexName,
            [
                'replicas' => array_keys($replicaNamesWithRankingAttributes),
            ],
        );

        // must wait for index to be created before creating suggestion indexes and settings
        $searchClient->waitForTask($indexName, $response['taskID']);

        // Create settings for existing indexes
        $response = $searchClient->setSettings($indexName, $this->getSettings($locale), true);

        // replica configuration can be queued for later execution
        $this->configureReplicasRankingAttributes(
            $replicaNamesWithRankingAttributes,
            $searchClient,
        );

        $searchClient->waitForTask($indexName, $response['taskID']);

        $this->suggestionIndexHandler->createProductSuggestionsIndex($indexName, $searchClient);
    }

    /**
     * @return array<string, mixed>
     */
    public function getSettings(string $locale): array
    {
        $indexQueryLanguages = [$this->extractLanguageFromLocale($locale)];

        return [
            'renderingContent' => [
                'facetOrdering' => [
                    'facets' => [
                        'order' => $this->getFilterableNameAttributes(),
                    ],
                ],
            ],
            'searchableAttributes' => $this->algoliaConfig->getSearchableAttributes(),
            'attributesForFaceting' => $this->algoliaConfig->getFilterableAttributes(),
            'attributeForDistinct' => static::ATTRIBUTE_NAME_PRODUCT_ABSTRACT_SKU,
            'distinct' => true,
            'indexLanguages' => $indexQueryLanguages,
            'queryLanguages' => $indexQueryLanguages,
        ];
    }

    /**
     * @return array<string>
     */
    protected function getFilterableNameAttributes(): array
    {
        return array_values(array_filter(array_map(
            function (string $attribute): string {
                if (in_array($attribute, $this->algoliaConfig->getNonDisplayAttributes(), true)) {
                    return '';
                }
                preg_match('/\((?<attr>[^()]+)\)/', $attribute, $matches);

                return $matches['attr'] ?? '';
            },
            $this->algoliaConfig->getFilterableAttributes(),
        )));
    }

    /**
     * @return array<array>
     */
    public function getReplicaNameWithRankingAttributes(string $indexName, string $attributeName): array
    {
        $replicasIndexNames = [];
        $replicaDescName = $this->getReplicaNameAttributeDesc($indexName, $attributeName);
        $replicaAscName = $this->getReplicaNameAttributeAsc($indexName, $attributeName);
        $replicasIndexNames[$replicaDescName] = [
            $this->getRankingByAttributeDesc($attributeName),
            ...$this->getDefaultRankingOrder(),
        ];
        $replicasIndexNames[$replicaAscName] = [
            $this->getRankingByAttributeAsc($attributeName),
            ...$this->getDefaultRankingOrder(),
        ];

        return $replicasIndexNames;
    }

    /**
     * @return array<array>
     */
    protected function getReplicaNamesWithRankingAttributes(string $indexName, AlgoliaConfigTransfer $algoliaConfigTransfer): array
    {
        $replicaNamesWithRankingAttributes = [
            $this->getReplicaNameAttributeDesc($indexName, static::ATTRIBUTE_NAME_RATING) => [
                $this->getRankingByAttributeDesc(static::ATTRIBUTE_NAME_RATING),
                ...$this->getDefaultRankingOrder(),
            ],
            $this->getReplicaNameAttributeAsc($indexName, static::ATTRIBUTE_NAME_RATING) => [
                $this->getRankingByAttributeAsc(static::ATTRIBUTE_NAME_RATING),
                ...$this->getDefaultRankingOrder(),
            ],
            $this->getReplicaNameAttributeDesc($indexName, static::ATTRIBUTE_NAME_NAME) => [
                $this->getRankingByAttributeDesc(static::ATTRIBUTE_NAME_ABSTRACT_NAME),
                ...$this->getDefaultRankingOrder(),
            ],
            $this->getReplicaNameAttributeAsc($indexName, static::ATTRIBUTE_NAME_NAME) => [
                $this->getRankingByAttributeAsc(static::ATTRIBUTE_NAME_ABSTRACT_NAME),
                ...$this->getDefaultRankingOrder(),
            ],
        ];

        if ($algoliaConfigTransfer->getIsProductPriceSynced()) {
            $replicaNamesWithRankingAttributes = array_merge(
                $replicaNamesWithRankingAttributes,
                [
                    $this->getReplicaNameAttributeAsc($indexName, static::ATTRIBUTE_NAME_PRICES_EUR_GROSS) => [
                        $this->getRankingByAttributeAsc(static::ATTRIBUTE_NAME_PRICES_EUR_GROSS),
                        ...$this->getDefaultRankingOrder(),
                    ],
                    $this->getReplicaNameAttributeDesc($indexName, static::ATTRIBUTE_NAME_PRICES_EUR_GROSS) => [
                        $this->getRankingByAttributeDesc(static::ATTRIBUTE_NAME_PRICES_EUR_GROSS),
                        ...$this->getDefaultRankingOrder(),
                    ],
                    $this->getReplicaNameAttributeAsc($indexName, static::ATTRIBUTE_NAME_PRICES_EUR_NET) => [
                        $this->getRankingByAttributeAsc(static::ATTRIBUTE_NAME_PRICES_EUR_NET),
                        ...$this->getDefaultRankingOrder(),
                    ],
                    $this->getReplicaNameAttributeDesc($indexName, static::ATTRIBUTE_NAME_PRICES_EUR_NET) => [
                        $this->getRankingByAttributeDesc(static::ATTRIBUTE_NAME_PRICES_EUR_NET),
                        ...$this->getDefaultRankingOrder(),
                    ],
                ],
            );
        }

        return $replicaNamesWithRankingAttributes;
    }

    /**
     * @param array<string, array<string>> $replicaNamesWithRankingAttributes
     */
    protected function configureReplicasRankingAttributes(
        array $replicaNamesWithRankingAttributes,
        SearchClient $searchClient
    ): IndexConfigurationResponseTransfer {
        $indexConfigurationResponse = (new IndexConfigurationResponseTransfer())
            ->setIsSuccessful(true);

        foreach ($replicaNamesWithRankingAttributes as $replicaName => $rankingAttributes) {
            $indexConfigurationResponse = $this->setIndexSettings(
                $searchClient,
                $replicaName,
                [
                    'ranking' => $rankingAttributes,
                ],
            );

            if (!$indexConfigurationResponse->getIsSuccessful()) {
                return $indexConfigurationResponse;
            }
        }

        return $indexConfigurationResponse;
    }

    protected function getReplicaNameAttributeDesc(string $indexName, string $attributeName): string
    {
        return sprintf('%s-desc-%s', $indexName, $attributeName);
    }

    protected function getReplicaNameAttributeAsc(string $indexName, string $attributeName): string
    {
        return sprintf('%s-asc-%s', $indexName, $attributeName);
    }

    protected function getRankingByAttributeDesc(string $attributeName): string
    {
        return sprintf('desc(%s)', $attributeName);
    }

    protected function getRankingByAttributeAsc(string $attributeName): string
    {
        return sprintf('asc(%s)', $attributeName);
    }

    /**
     * @return array<string>
     */
    protected function getDefaultRankingOrder(): array
    {
        return [
            'typo',
            'geo',
            'words',
            'filters',
            'proximity',
            'attribute',
            'exact',
            'custom',
        ];
    }

    /**
     * @param array<string, mixed> $settings
     */
    protected function setIndexSettings(SearchClient $searchClient, string $indexName, array $settings): IndexConfigurationResponseTransfer
    {
        $indexConfigurationResponseTransfer = (new IndexConfigurationResponseTransfer())
            ->setIsSuccessful(true);

        try {
            $searchClient->setSettings($indexName, $settings);
        } catch (AlgoliaException $e) {
            $this->getLogger()->error(sprintf('Algolia setSettings failed for index %s', $indexName), ['exception' => $e]);

            return $indexConfigurationResponseTransfer
                ->setIsSuccessful(false)
                ->setErrorMessage($this->getFormattedErrorMessage($indexName, $e->getMessage()));
        }

        return $indexConfigurationResponseTransfer;
    }

    protected function getFormattedErrorMessage(string $indexName, string $errorMessage): string
    {
        return sprintf(static::ERROR_MESSAGE_TEMPLATE, $indexName, $errorMessage);
    }

    protected function extractLanguageFromLocale(string $locale): string
    {
        return Locale::parseLocale($locale)[Locale::LANG_TAG];
    }
}
