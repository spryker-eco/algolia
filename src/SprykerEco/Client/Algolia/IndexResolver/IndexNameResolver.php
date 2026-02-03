<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Algolia\IndexResolver;

use ArrayObject;
use Exception;
use Generated\Shared\Transfer\AlgoliaIndicesCollectionTransfer;
use Generated\Shared\Transfer\FacetCollectionTransfer;
use Generated\Shared\Transfer\SortingEntryTransfer;
use SprykerEco\Client\Algolia\AlgoliaConfig;
use SprykerEco\Shared\Algolia\Enum\AlgoliaEntityNameEnum;

class IndexNameResolver implements IndexNameResolverInterface
{
    /**
     * @var string
     */
    protected const INDEX_NAME_TEMPLATE = '%s-%s-%s-%s';

    /**
     * @var string
     */
    protected const CMS_PAGE_INDEX_NAME_TEMPLATE = '%s-%s-%s';

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

    /**
     * @param string|null $entityName
     * @param string|null $storeName
     */
    public function filterIndicesByIndexNameParts(
        AlgoliaIndicesCollectionTransfer $algoliaIndicesCollectionTransfer,
        string $tenantIdentifier,
        ?string $entityName = null,
        ?string $storeName = null
    ): AlgoliaIndicesCollectionTransfer {
        $tenantIdentifier = strtolower($tenantIdentifier);

        $filteredAlgoliaIndicesCollectionTransfer = new AlgoliaIndicesCollectionTransfer();

        foreach ($algoliaIndicesCollectionTransfer->getIndices() as $index) {
            if (!str_contains($index->getName(), $tenantIdentifier)) {
                continue;
            }

            if ($entityName && !str_contains($index->getName(), $entityName)) {
                continue;
            }

            if ($storeName && !str_contains($index->getName(), strtolower($storeName))) {
                continue;
            }

            $filteredAlgoliaIndicesCollectionTransfer->addIndex($index);
        }

        return $filteredAlgoliaIndicesCollectionTransfer;
    }

    public function getIndexReplicaNameForSorting(
        string $indexName,
        SortingEntryTransfer $sortingEntryTransfer,
        FacetCollectionTransfer $facetCollectionTransfer
    ): string {
        $fieldKey = $sortingEntryTransfer->getField();

        if ($fieldKey === AlgoliaConfig::FILTER_NAME_PRICE) {
            $fieldKey = strtolower($this->algoliaConfig->getPriceFacetKey($facetCollectionTransfer));
        }

        if ($sortingEntryTransfer->getDirection() === 'asc') {
            return sprintf(AlgoliaConfig::ALGOLIA_INDEX_REPLICA_NAME_TEMPLATE_SORT_ASC, $indexName, $fieldKey);
        }

        return sprintf(AlgoliaConfig::ALGOLIA_INDEX_REPLICA_NAME_TEMPLATE_SORT_DESC, $indexName, $fieldKey);
    }

    protected function createProductIndexFromTemplate(
        string $tenantIdentifier,
        string $storeName,
        string $locale
    ): string {
        return sprintf(
            static::INDEX_NAME_TEMPLATE,
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
            static::CMS_PAGE_INDEX_NAME_TEMPLATE,
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
            $this->algoliaConfig->getQuerySuggestionsSuffix(),
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
}
