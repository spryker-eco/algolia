<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Algolia\IndexResolver;

use ArrayObject;
use Generated\Shared\Transfer\FacetCollectionTransfer;
use Generated\Shared\Transfer\SortingEntryTransfer;

interface IndexNameResolverInterface
{
    public function resolveProductIndexName(
        string $tenantIdentifier,
        string $storeName,
        string $locale
    ): string;

    public function resolveProductsSuggestionIndexNameFromProductIndexName(string $indexName): string;

    public function resolveCmsPageIndexName(
        string $locale,
        string $tenantIdentifier
    ): string;

    public function getIndexReplicaNameForSorting(
        string $indexName,
        SortingEntryTransfer $sortingEntryTransfer,
        FacetCollectionTransfer $facetCollectionTransfer,
        string $sourceIdentifier
    ): string;

    /**
     * @param \ArrayObject<\Generated\Shared\Transfer\EntityToIndexMappingTransfer> $entityToIndexMappings
     *
     * @throws \Exception
     */
    public function resolveIndexNameByMapping(string $sourceIdentifier, string $storeName, string $locale, ArrayObject $entityToIndexMappings): string;
}
