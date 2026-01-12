<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Exporter;

use Generated\Shared\Transfer\AlgoliaResponseTransfer;
use Generated\Shared\Transfer\ProductExportedTransfer;
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

    public function exportProducts(ProductExportedTransfer $productExportedTransfer): AlgoliaResponseTransfer
    {
        $algoliaConfigTransfer = $this->algoliaConfigResolver->findConfig();
        if ($algoliaConfigTransfer === null) {
            return (new AlgoliaResponseTransfer())->setIsSuccessful(true);
        }

        $productExportedTransfer->setProductsConcrete(
            $this->productDataFilterApplier->apply($productExportedTransfer->getProductsConcrete()),
        );

        $productExportedTransfer->setProductsConcrete(
            $this->inactiveProductFilter->filterIndexableProductsConcrete($productExportedTransfer->getProductsConcrete(), $algoliaConfigTransfer),
        );

        $indexedAlgoliaProductCollections = $this->algoliaProductIndexer->indexProductsConcreteByStoreAndLocale(
            $productExportedTransfer->getProductsConcrete(),
            $algoliaConfigTransfer->getTenantIdentifier(),
        );

        return $this->algoliaProductSaver->saveAlgoliaProducts(
            $indexedAlgoliaProductCollections,
            $algoliaConfigTransfer,
        );
    }
}
