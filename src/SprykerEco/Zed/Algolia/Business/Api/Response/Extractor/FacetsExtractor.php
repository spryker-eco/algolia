<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Api\Response\Extractor;

use Generated\Shared\Transfer\AlgoliaSearchResponseTransfer;
use Generated\Shared\Transfer\SearchRequestTransfer;
use SprykerEco\Shared\Algolia\Enum\AlgoliaEntityNameEnum;
use SprykerEco\Zed\Algolia\AlgoliaConfig;
use SprykerEco\Zed\Algolia\Business\Resolver\AlgoliaConfigResolverInterface;

class FacetsExtractor implements FacetsExtractorInterface
{
    /**
     * @var string
     */
    protected const RANGE_FACET_KEY_FROM = 'from';

    /**
     * @var string
     */
    protected const RANGE_FACET_KEY_TO = 'to';

    /**
     * @var string
     */
    protected const RESPONSE_FACETS_FIELD_MAX = 'max';

    /**
     * @var string
     */
    protected const RESPONSE_FACETS_FIELD_MIN = 'min';

    /**
     * @var string
     */
    protected const RESPONSE_FIELD_FACETS = 'facets';

    /**
     * @var string
     */
    protected const RESPONSE_FIELD_FACETS_STATS = 'facets_stats';

    /**
     * @var string
     */
    protected const RESPONSE_FIELD_PREFIX_ATTRIBUTES = 'attributes.';

    public function __construct(protected AlgoliaConfig $algoliaConfig, protected AlgoliaConfigResolverInterface $algoliaConfigResolver)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function extract(AlgoliaSearchResponseTransfer $algoliaSearchResponseTransfer, SearchRequestTransfer $searchRequestTransfer): array
    {
        $responseFacets = $this->extractFacets($algoliaSearchResponseTransfer->getSearchResults());
        $responseFacetsStats = $this->extractFacetsStats($algoliaSearchResponseTransfer->getSearchResults(), $searchRequestTransfer);
        $algoliaConfigTransfer = $this->algoliaConfigResolver->findConfig();
        if ($algoliaConfigTransfer === null) {
            return [];
        }

        $facets = array_merge($responseFacets, $responseFacetsStats);
        $facetsOrder = array_map(
            function (string $facet): string {
                $facetKey = $this->adjustFacetKey($facet);

                return $facetKey === 'prices' || str_starts_with($facetKey, 'prices.') ? AlgoliaConfig::FILTER_NAME_PRICE : $facetKey;
            },
            $algoliaSearchResponseTransfer->getSearchResults()['renderingContent']['facetOrdering']['facets']['order'] ?? $this->algoliaConfig->getFilterableNameAttributes($algoliaConfigTransfer),
        );

//        if (isset($algoliaSearchResponseTransfer->getSearchResults()['renderingContent']['facetOrdering']['facets']['order'])) {
//            // Filter out facets that are not in the renderingContent
//            $facets = array_filter($facets, function ($key) use ($facetsOrder) {
//                foreach ($facetsOrder as $orderKey) {
//                    if ($key === $orderKey || str_starts_with($key, $orderKey . '.')) {
//                        return true;
//                    }
//                }
//            }, ARRAY_FILTER_USE_KEY);
//        }

        $this->sortArrayByKeys($facets, $facetsOrder);

        return $facets;
    }

    /**
     * @param $array
     * @param $order
     *
     * @return void
     */
    protected function sortArrayByKeys(&$array, $order)
    {
        $orderIndex = array_flip($order);
        uksort($array, function ($a, $b) use ($orderIndex): int {
            $indexA = $orderIndex[$a] ?? PHP_INT_MAX;
            $indexB = $orderIndex[$b] ?? PHP_INT_MAX;

            return ($indexA < $indexB) ? -1 : 1;
        });
    }

    /**
     * @param array<string, mixed> $searchResults
     *
     * @return array<string, mixed>
     */
    protected function extractFacets(array $searchResults): array
    {
        $facetData = [];

        if (!isset($searchResults[static::RESPONSE_FIELD_FACETS])) {
            return [];
        }

        foreach ($searchResults[static::RESPONSE_FIELD_FACETS] as $facetKey => $facetValues) {
            $facetKey = $this->adjustFacetKey($facetKey);

            if ($this->isFacetRestricted($facetKey)) {
                continue;
            }

            $facetData[$facetKey] = $facetValues;
        }

        return $facetData;
    }

    /**
     * @param array<string, mixed> $searchResults

     * @return array<string, array<string, int>>
     */
    protected function extractFacetsStats(array $searchResults, SearchRequestTransfer $searchRequestTransfer): array
    {
        $facetData = [];

        if (!isset($searchResults[static::RESPONSE_FIELD_FACETS_STATS])) {
            return [];
        }

        foreach ($searchResults[static::RESPONSE_FIELD_FACETS_STATS] as $facetKey => $facetValues) {
            $facetKey = $this->adjustFacetKey($facetKey);

            if ($this->isFacetRestricted($facetKey)) {
                continue;
            }

            $rangeFacetStats = $this->extractRangeFacetStats($facetValues);
            $facetData[$facetKey] = $rangeFacetStats;
        }
        if ($searchRequestTransfer->getSourceIdentifier() === AlgoliaEntityNameEnum::PRODUCT->value) {
            $facetData[AlgoliaConfig::FILTER_NAME_PRICE] = $this->getCurrentPriceFacetStats($searchResults[static::RESPONSE_FIELD_FACETS_STATS], $searchRequestTransfer);
        }

        return $facetData;
    }

    /**
     * @param array<string, int> $facetStats
     *
     * @return array<string, int>
     */
    protected function extractRangeFacetStats(array $facetStats): array
    {
        return [
            static::RANGE_FACET_KEY_FROM => $facetStats[static::RESPONSE_FACETS_FIELD_MIN] ?? 0,
            static::RANGE_FACET_KEY_TO => $facetStats[static::RESPONSE_FACETS_FIELD_MAX] ?? 0,
        ];
    }

    protected function adjustFacetKey(string $facetKey): string
    {
        if (str_starts_with($facetKey, static::RESPONSE_FIELD_PREFIX_ATTRIBUTES)) {
            return substr($facetKey, strlen(static::RESPONSE_FIELD_PREFIX_ATTRIBUTES));
        }

        return $facetKey;
    }

    protected function isFacetRestricted(string $facetKey): bool
    {
        foreach ($this->algoliaConfig->getRestrictedFacetKeys() as $restrictedFacetKey) {
            if (str_starts_with($facetKey, $restrictedFacetKey)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed> $facetsStats

     * @return array<int>
     */
    protected function getCurrentPriceFacetStats(array $facetsStats, SearchRequestTransfer $searchRequestTransfer): array
    {
        if (!$searchRequestTransfer->getFacets()) {
            return [];
        }

        $priceFacetKey = $this->algoliaConfig->getPriceFacetKey($searchRequestTransfer->getFacets());

        return $this->extractRangeFacetStats($facetsStats[$priceFacetKey] ?? []);
    }
}
