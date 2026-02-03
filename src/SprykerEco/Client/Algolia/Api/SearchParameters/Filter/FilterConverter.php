<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Algolia\Api\SearchParameters\Filter;

use ArrayObject;
use Generated\Shared\Transfer\FacetEntryTransfer;
use Generated\Shared\Transfer\FacetParametersTransfer;
use Generated\Shared\Transfer\SearchRequestTransfer;
use SprykerEco\Client\Algolia\AlgoliaConfig;
use SprykerEco\Client\Algolia\Exception\FacetTypeUnknownException;
use SprykerEco\Client\Algolia\IndexResolver\SearchIndexResolverInterface;
use SprykerEco\Client\Algolia\Resolver\AlgoliaConfigResolverInterface;
use SprykerEco\Shared\Algolia\Enum\AlgoliaEntityNameEnum;
use Symfony\Component\Cache\Adapter\AbstractAdapter;

class FilterConverter implements FilterConverterInterface
{
    /**
     * @var string
     */
    protected const FACET_TYPE_RANGE = 'range';

    /**
     * @var string
     */
    protected const FACET_TYPE_VALUES = 'values';

    /**
     * @var string
     */
    protected const KEY_FACET_CACHE = 'algolia.facets.%s';

    /**
     * @var int
     */
    protected const EXPIRES_AFTER = 3600;

    public function __construct(
        protected AlgoliaConfig $algoliaConfig,
        protected AlgoliaConfigResolverInterface $configResolver,
        protected SearchIndexResolverInterface $searchIndexResolver,
        protected AbstractAdapter $cache
    ) {
    }

    public function convertFacetCollectionTransferToAlgoliaFiltersString(SearchRequestTransfer $searchRequestTransfer): string
    {
        $facetCollectionTransfer = $searchRequestTransfer->getFacets();

        if ($facetCollectionTransfer === null || $facetCollectionTransfer->getFacets()->count() === 0) {
            return '';
        }

        $filters = [];
        $facetWhiteList = $this->getFacetWhiteList($searchRequestTransfer);

        if (
            in_array(AlgoliaConfig::ATTRIBUTE_NAME_PRICES, $facetWhiteList) &&
            $searchRequestTransfer->getSourceIdentifier() === AlgoliaEntityNameEnum::PRODUCT->value
        ) {
            $filters[] = sprintf('%s>=0', $this->algoliaConfig->getPriceFacetKey($facetCollectionTransfer));
        }
        foreach ($facetCollectionTransfer->getFacets() as $fieldKey => $facetEntryTransfer) {
            $mappedFieldKey = $this->algoliaConfig->getAlgoliaFacetFieldKey($fieldKey, $facetCollectionTransfer);

            $isInWhiteList = array_filter($facetWhiteList, fn (string $facet): bool => str_starts_with($mappedFieldKey, $facet));
            if (
                $searchRequestTransfer->getSourceIdentifier() === AlgoliaEntityNameEnum::PRODUCT->value &&
                (!$mappedFieldKey || !$isInWhiteList)
            ) {
                continue;
            }

            $filters[] = $this->getFilter($mappedFieldKey, $facetEntryTransfer);
        }

        return implode(' AND ', array_filter($filters));
    }

    /**
     * @throws \SprykerEco\Client\Algolia\Exception\FacetTypeUnknownException
     */
    public function getFilter(string $fieldKey, FacetEntryTransfer $facetEntryTransfer): string
    {
        if ($facetEntryTransfer->getType() === static::FACET_TYPE_RANGE) {
            return $this->getRangeFilter($fieldKey, $facetEntryTransfer->getParameters());
        }

        if ($facetEntryTransfer->getType() === static::FACET_TYPE_VALUES) {
            return $this->getValueFilter($fieldKey, $facetEntryTransfer->getParameters());
        }

        throw new FacetTypeUnknownException(sprintf('Facet type unknown, "%s" type given', $facetEntryTransfer->getType()));
    }

    /**
     * @return array
     */
    protected function getFacetWhiteList(SearchRequestTransfer $searchRequestTransfer): array
    {
        $algoliaConfigTransfer = $this->configResolver->getConfig();
        $item = $this->cache->getItem(sprintf(static::KEY_FACET_CACHE, $algoliaConfigTransfer->getTenantIdentifierOrFail()));
        $facetNames = $item->get();

        if ($facetNames === null) {
            $settings = $this->searchIndexResolver->getSearchIndexClientWithPrimarySearchIndex($searchRequestTransfer, $algoliaConfigTransfer)->getSettings();

            if (empty($settings['attributesForFaceting'])) {
                return [];
            }

            $facetNames = array_map(
                function (string $facet): string {
                    preg_match_all('/(?<attr>[0-9A-z_:.-]+)/i', $facet, $matches); // Facets name can contain: letters, digits and `_:.-' symbols.
                    if (empty($matches['attr'])) {
                        return '';
                    }

                    return end($matches['attr']);
                },
                $settings['attributesForFaceting'],
            );

            $item->set($facetNames);
            $item->expiresAfter(static::EXPIRES_AFTER);
            $this->cache->save($item);
        }

        return $facetNames;
    }

    protected function getRangeFilter(string $fieldKey, FacetParametersTransfer $facetParametersTransfer): string
    {
        if (!$facetParametersTransfer->getTo() && $facetParametersTransfer->getFrom() > $facetParametersTransfer->getTo()) {
            return sprintf('%s>=%f', $fieldKey, $facetParametersTransfer->getFrom());
        }

        return sprintf('%s:%f TO %f', $fieldKey, $facetParametersTransfer->getFrom(), $facetParametersTransfer->getTo());
    }

    protected function getValueFilter(string $fieldKey, FacetParametersTransfer $facetParametersTransfer): string
    {
        if (!count($facetParametersTransfer->getValues())) {
            return '';
        }
        $valuesIterator = (new ArrayObject($facetParametersTransfer->getValues()))->getIterator();

        $filterString = sprintf("%s:'%s'", $fieldKey, addslashes($valuesIterator->current()));

        if (count($facetParametersTransfer->getValues()) === 1) {
            return $filterString;
        }

        $valuesIterator->next();
        while ($valuesIterator->valid()) {
            $filterString .= sprintf(" OR %s:'%s'", $fieldKey, addslashes($valuesIterator->current()));
            $valuesIterator->next();
        }

        return '(' . $filterString . ')';
    }
}
