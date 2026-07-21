<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Algolia\IndexResolver;

use ArrayObject;
use Exception;
use Generated\Shared\Transfer\FacetCollectionTransfer;
use Generated\Shared\Transfer\SortingEntryTransfer;
use SprykerEco\Client\Algolia\AlgoliaConfig;
use SprykerEco\Shared\Algolia\Enum\AlgoliaEntityNameEnum;

class IndexNameResolver implements IndexNameResolverInterface
{
    protected const string QUERY_SUGGESTIONS_SUFFIX = 'query_suggestions';

    /**
     * @var string
     */
    protected const FILTER_NAME_PRICE = 'price';

    /**
     * @var array<string, string>
     */
    protected const PRICE_MODE_MAPPING = [
        'GROSS_MODE' => 'gross',
        'NET_MODE' => 'net',
    ];

    public function __construct(protected AlgoliaConfig $algoliaConfig)
    {
    }

    public function resolveProductIndexName(
        string $tenantIdentifier,
        string $storeName,
        string $locale
    ): string {
        return strtolower($this->createProductIndexFromTemplate(
            $tenantIdentifier,
            $storeName,
            $locale,
        ));
    }

    public function resolveProductsSuggestionIndexNameFromProductIndexName(string $indexName): string
    {
        return strtolower($this->createSuggestionIndexFromTemplate($indexName));
    }

    public function resolveCmsPageIndexName(
        string $locale,
        string $tenantIdentifier
    ): string {
        return strtolower($this->createCmsPageIndexFromTemplate(
            $tenantIdentifier,
            $locale,
        ));
    }

    public function getIndexReplicaNameForSorting(
        string $indexName,
        SortingEntryTransfer $sortingEntryTransfer,
        FacetCollectionTransfer $facetCollectionTransfer,
        string $sourceIdentifier
    ): string {
        $fieldKey = $sortingEntryTransfer->getField();

        if ($fieldKey === static::FILTER_NAME_PRICE) {
            $fieldKey = strtolower($this->getPriceFacetKey($facetCollectionTransfer));
        }

        $fieldKey = $this->mapSortFieldToAttribute($fieldKey, $sourceIdentifier);

        if ($sortingEntryTransfer->getDirection() === 'asc') {
            return sprintf('%s-asc-%s', $indexName, $fieldKey);
        }

        return sprintf('%s-desc-%s', $indexName, $fieldKey);
    }

    protected function mapSortFieldToAttribute(string $fieldKey, string $sourceIdentifier): string
    {
        $mapping = match ($sourceIdentifier) {
            AlgoliaEntityNameEnum::PRODUCT->value => $this->algoliaConfig->getProductSortingParamToAttributeMapping(),
            AlgoliaEntityNameEnum::CMS_PAGE->value => $this->algoliaConfig->getCmsPageSortingParamToAttributeMapping(),
            default => [],
        };

        return $mapping[$fieldKey] ?? $fieldKey;
    }

    protected function createProductIndexFromTemplate(
        string $tenantIdentifier,
        string $storeName,
        string $locale
    ): string {
        return sprintf(
            '%s-%s-%s-%s',
            $tenantIdentifier,
            AlgoliaEntityNameEnum::PRODUCT->value,
            $storeName,
            $locale,
        );
    }

    protected function createCmsPageIndexFromTemplate(
        string $tenantIdentifier,
        string $locale
    ): string {
        return sprintf(
            '%s-%s-%s',
            $tenantIdentifier,
            AlgoliaEntityNameEnum::CMS_PAGE->value,
            $locale,
        );
    }

    protected function createSuggestionIndexFromTemplate(string $indexName): string
    {
        return sprintf(
            '%s_%s',
            $indexName,
            static::QUERY_SUGGESTIONS_SUFFIX,
        );
    }

    /**
     * @param \ArrayObject<\Generated\Shared\Transfer\EntityToIndexMappingTransfer> $entityToIndexMappings
     *
     * @throws \Exception
     */
    public function resolveIndexNameByMapping(string $sourceIdentifier, string $storeName, string $locale, ArrayObject $entityToIndexMappings): string
    {
        foreach ($entityToIndexMappings as $entityToIndexMappingTransfer) {
            if (
                $entityToIndexMappingTransfer->getSourceIdentifier() === $sourceIdentifier
                && in_array(strtolower($entityToIndexMappingTransfer->getStore()), [strtolower($storeName), '*'])
                && array_intersect($entityToIndexMappingTransfer->getLocales(), [$locale, '*'])
            ) {
                return $entityToIndexMappingTransfer->getIndexName();
            }
        }

        throw new Exception(sprintf("Index mapping not found for '%s', store '%s' and locale '%s'.", $sourceIdentifier, $storeName, $locale));
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
