<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Updater;

use ArrayObject;
use Generated\Shared\Transfer\AlgoliaConfigTransfer;
use Generated\Shared\Transfer\AlgoliaResponseTransfer;
use Generated\Shared\Transfer\ProductDeletedTransfer;
use SprykerEco\Zed\Algolia\Business\Deleter\ProductDeleterInterface;
use SprykerEco\Zed\Algolia\Business\Filter\ProductConcreteFilterInterface;
use SprykerEco\Zed\Algolia\Business\Filter\ProductDataFilterApplierInterface;
use SprykerEco\Zed\Algolia\Business\Indexer\ProductIndexerInterface;
use SprykerEco\Zed\Algolia\Business\Resolver\AlgoliaConfigResolverInterface;
use SprykerEco\Zed\Algolia\Business\Saver\ProductSaverInterface;

class ProductUpdater implements ProductUpdaterInterface
{
    /**
     * @var string
     */
    protected const ALL_STORES = '*';

    public function __construct(
        protected ProductIndexerInterface $algoliaProductIndexer,
        protected ProductSaverInterface $algoliaProductSaver,
        protected ProductDeleterInterface $productDeleter,
        protected ProductConcreteFilterInterface $productConcreteFilter,
        protected ProductDataFilterApplierInterface $productDataFilterApplier,
        protected AlgoliaConfigResolverInterface $algoliaConfigResolver
    ) {
    }

    /**
     * @param \ArrayObject<\Generated\Shared\Transfer\ProductConcreteTransfer> $productConcreteTransfers
     */
    public function updateProducts(ArrayObject $productConcreteTransfers): AlgoliaResponseTransfer
    {
        $filteredProductsConcrete = $this->productDataFilterApplier->apply($productConcreteTransfers);
        $algoliaConfigTransfer = $this->algoliaConfigResolver->getConfig();

        $notApplicableProductsConcrete = $this->productConcreteFilter->filterNonIndexableProductsConcrete(
            $filteredProductsConcrete,
            $algoliaConfigTransfer,
        );

        if (count($notApplicableProductsConcrete) > 0) {
            $this->deleteInactiveProductConcrete(
                $notApplicableProductsConcrete,
                $algoliaConfigTransfer,
            );
        }

        $applicableProductsConcrete = $this->productConcreteFilter->filterIndexableProductsConcrete(
            $filteredProductsConcrete,
            $algoliaConfigTransfer,
        );

        $indexedAlgoliaProductCollectionTransfer = $this->algoliaProductIndexer->indexProductsConcreteByStoreAndLocale(
            $applicableProductsConcrete,
            $algoliaConfigTransfer->getTenantIdentifierOrFail(),
        );

        return $this->algoliaProductSaver->saveAlgoliaProducts(
            $indexedAlgoliaProductCollectionTransfer,
            $algoliaConfigTransfer,
        );
    }

    /**
     * @param \ArrayObject<\Generated\Shared\Transfer\ProductConcreteTransfer> $productConcreteTransfers
     */
    protected function deleteInactiveProductConcrete(
        ArrayObject $productConcreteTransfers,
        AlgoliaConfigTransfer $algoliaConfigTransfer
    ): void {
        $productDeletedTransfersIndexedByStore = [];
        foreach ($productConcreteTransfers as $productConcreteTransfer) {
            if (!count($productConcreteTransfer->getStores())) {
                $productDeletedTransfersIndexedByStore[static::ALL_STORES][] = (new ProductDeletedTransfer())
                    ->setSku($productConcreteTransfer->getSku());

                continue;
            }
            foreach ($productConcreteTransfer->getStores() as $storeTransfer) {
                $productDeletedTransfersIndexedByStore[$storeTransfer->getName()][] = (new ProductDeletedTransfer())
                    ->setSku($productConcreteTransfer->getSku());
            }
        }

        foreach ($productDeletedTransfersIndexedByStore as $storeName => $productDeletedTransfers) {
            $this->productDeleter->deleteProducts(
                $productDeletedTransfers,
                $algoliaConfigTransfer,
                $storeName !== static::ALL_STORES ? $storeName : null,
            );
        }
    }
}
