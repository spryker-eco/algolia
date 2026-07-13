<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Algolia\Api\SearchParameters\Filter;

use ArrayObject;
use Generated\Shared\Transfer\FacetCollectionTransfer;
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

    /**
     * @var string
     */
    protected const FILTER_NAME_PRICE = 'price';

    /**
     * @var array<string>
     */
    protected const NON_ATTRIBUTE_FIELDS = [
        'product_abstract_sku',
        'sku',
        'name',
        'description',
        'keywords',
        'abstract_name',
        'merchant_name',
        'merchant_reference',
        'category',
        'hierarchicalCategories',
        'images',
        'label',
        'prices',
        'rating',
        'url',
        'concrete_prices',
    ];

    /**
     * @var array<string, string>
     */
    protected const PRICE_MODE_MAPPING = [
        'GROSS_MODE' => 'gross',
        'NET_MODE' => 'net',
    ];

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
            in_array('prices', $facetWhiteList) &&
            $searchRequestTransfer->getSourceIdentifier() === AlgoliaEntityNameEnum::PRODUCT->value
        ) {
            $filters[] = sprintf('%s>=0', $this->getPriceFacetKey($facetCollectionTransfer));
        }
        foreach ($facetCollectionTransfer->getFacets() as $fieldKey => $facetEntryTransfer) {
            $mappedFieldKey = $this->getAlgoliaFacetFieldKey($fieldKey, $facetCollectionTransfer);

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

    protected function getAlgoliaFacetFieldKey(string $fieldKey, FacetCollectionTransfer $facetCollectionTransfer): string
    {
        if ($fieldKey === static::FILTER_NAME_PRICE) {
            return $this->getPriceFacetKey($facetCollectionTransfer);
        }

        if (in_array($fieldKey, static::NON_ATTRIBUTE_FIELDS, true)) {
            return $fieldKey;
        }

        if (str_starts_with($fieldKey, 'search_metadata.')) {
            return $fieldKey;
        }

        if (str_starts_with($fieldKey, 'search_metadata_')) {
            // This is needed to support SCOS request format coming from different applications
            return 'search_metadata.' . substr($fieldKey, strlen('search_metadata_'));
        }

        return 'attributes.' . $fieldKey;
    }

    protected function getPriceFacetKey(FacetCollectionTransfer $facetCollectionTransfer): string
    {
        $facetsTransfers = $facetCollectionTransfer->getFacets();

        if (
            $facetsTransfers->offsetExists('currency')
            && $facetsTransfers->offsetExists('price_mode')
        ) {
            $currency = $facetsTransfers->offsetGet('currency')->getParameters()->getValues()[0];
            $pricingMode = $facetsTransfers->offsetGet('price_mode')->getParameters()->getValues()[0];

            return sprintf('prices.%s.%s', strtolower($currency), static::PRICE_MODE_MAPPING[$pricingMode] ?? 'gross');
        }

        return static::FILTER_NAME_PRICE;
    }
}
