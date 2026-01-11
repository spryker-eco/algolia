<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Indexer;

use ArrayObject;
use Generated\Shared\Transfer\IndexedAlgoliaProductCollectionTransfer;
use SprykerEco\Zed\Algolia\Business\IndexResolver\IndexNameResolver;
use SprykerEco\Zed\Algolia\Business\Mapper\ProductMapperInterface;

class ProductIndexer implements ProductIndexerInterface
{
    public function __construct(
        protected ProductMapperInterface $algoliaProductMapper,
        protected IndexNameResolver $algoliaIndexNameResolver
    ) {
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\ProductConcreteTransfer> $productsConcrete
     * @param string $tenantIdentifier
     *
     * @return array<int, \Generated\Shared\Transfer\IndexedAlgoliaProductCollectionTransfer>
     */
    public function indexProductsConcreteByStoreAndLocale(
        ArrayObject $productsConcrete,
        string $tenantIdentifier
    ): array {
        $algoliaProductTransfersIndexedByStoreAndLocale = $this->mapProductsConcreteToIndexedAlgoliaProductTransfersArray(
            $productsConcrete,
        );

        return $this->getIndexedAlgoliaProductCollectionTransfers(
            $algoliaProductTransfersIndexedByStoreAndLocale,
            $tenantIdentifier,
        );
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\ProductConcreteTransfer> $productsConcrete
     *
     * @return array<string, array<string, array<\Generated\Shared\Transfer\AlgoliaProductTransfer>>>
     */
    protected function mapProductsConcreteToIndexedAlgoliaProductTransfersArray(
        ArrayObject $productsConcrete
    ): array {
        $indexedAlgoliaProductTransfersArray = [];
        foreach ($productsConcrete as $productConcreteTransfer) {
            $indexedAlgoliaProductTransfersArray = $this->algoliaProductMapper
                ->mapProductConcreteToAlgoliaProductTransfersArrayIndexedByStoreAndLocale(
                    $productConcreteTransfer,
                    $indexedAlgoliaProductTransfersArray,
                );
        }

        return $indexedAlgoliaProductTransfersArray;
    }

    /**
     * @param array<string, array<string, array<\Generated\Shared\Transfer\AlgoliaProductTransfer>>> $algoliaProductTransfersIndexedByStoreAndLocale
     * @param string $tenantIdentifier
     *
     * @return array<\Generated\Shared\Transfer\IndexedAlgoliaProductCollectionTransfer>
     */
    protected function getIndexedAlgoliaProductCollectionTransfers(
        array $algoliaProductTransfersIndexedByStoreAndLocale,
        string $tenantIdentifier
    ): array {
        $indexedAlgoliaProductsArray = [];
        foreach ($algoliaProductTransfersIndexedByStoreAndLocale as $storeName => $algoliaProductTransfersIndexedByLocale) {
            foreach ($algoliaProductTransfersIndexedByLocale as $locale => $algoliaProductTransfers) {
                $algoliaIndexName = $this->algoliaIndexNameResolver->resolveProductIndexName(
                    $tenantIdentifier,
                    $storeName,
                    $locale,
                );

                $indexedAlgoliaProductsArray[] = (new IndexedAlgoliaProductCollectionTransfer())
                    ->setIndexName($algoliaIndexName)
                    ->setLocale($locale)
                    ->setTenantIdentifier($tenantIdentifier)
                    ->setAlgoliaProducts(new ArrayObject($algoliaProductTransfers));
            }
        }

        return $indexedAlgoliaProductsArray;
    }
}
