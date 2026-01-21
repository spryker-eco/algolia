<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Exporter;

use ArrayObject;
use Generated\Shared\Transfer\AlgoliaResponseTransfer;
use SprykerEco\Zed\Algolia\Business\Filter\ProductConcreteFilterInterface;
use SprykerEco\Zed\Algolia\Business\Filter\ProductDataFilterApplierInterface;
use SprykerEco\Zed\Algolia\Business\Indexer\ProductIndexerInterface;
use SprykerEco\Zed\Algolia\Business\Resolver\AlgoliaConfigResolverInterface;
use SprykerEco\Zed\Algolia\Business\Saver\ProductSaverInterface;

class ProductExporter implements ProductExporterInterface
{
    public function __construct(
        protected ProductIndexerInterface $algoliaProductIndexer,
        protected ProductSaverInterface $algoliaProductSaver,
        protected ProductConcreteFilterInterface $inactiveProductFilter,
        protected ProductDataFilterApplierInterface $productDataFilterApplier,
        protected AlgoliaConfigResolverInterface $algoliaConfigResolver
    ) {
    }

    /**
     * @param \ArrayObject<\Generated\Shared\Transfer\ProductConcreteTransfer> $productConcreteTransfers
     *
     * @return \Generated\Shared\Transfer\AlgoliaResponseTransfer
     */
    public function exportProducts(ArrayObject $productConcreteTransfers): AlgoliaResponseTransfer
    {
        $algoliaConfigTransfer = $this->algoliaConfigResolver->findConfig();
        if ($algoliaConfigTransfer === null) {
            return (new AlgoliaResponseTransfer())->setIsSuccessful(false);
        }

        $productConcreteTransfers = $this->productDataFilterApplier->apply($productConcreteTransfers);

        $productConcreteTransfers = $this->inactiveProductFilter->filterIndexableProductsConcrete(
            $productConcreteTransfers,
            $algoliaConfigTransfer,
        );

        if ($productConcreteTransfers->count() === 0) {
            return (new AlgoliaResponseTransfer())
                ->setResponseMessage('No products to export.')
                ->setIsSuccessful(false);
        }

        $indexedAlgoliaProductCollections = $this->algoliaProductIndexer->indexProductsConcreteByStoreAndLocale(
            $productConcreteTransfers,
            $algoliaConfigTransfer->getTenantIdentifier(),
        );

        return $this->algoliaProductSaver->saveAlgoliaProducts(
            $indexedAlgoliaProductCollections,
            $algoliaConfigTransfer,
        );
    }
}
