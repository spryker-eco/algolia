<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Exporter;

use ArrayObject;
use Generated\Shared\Transfer\AlgoliaConfigTransfer;
use Generated\Shared\Transfer\AlgoliaExportCriteriaTransfer;
use Generated\Shared\Transfer\AlgoliaExportResultTransfer;
use Generated\Shared\Transfer\AlgoliaResponseTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use Generated\Shared\Transfer\ProductConcreteConditionsTransfer;
use Generated\Shared\Transfer\ProductConcreteCriteriaTransfer;
use Spryker\Zed\Product\Business\ProductFacadeInterface;
use SprykerEco\Zed\Algolia\Business\Api\Creator\SearchClientCreatorInterface;
use SprykerEco\Zed\Algolia\Business\Api\IndexConfigurator\IndexConfiguratorInterface;
use SprykerEco\Zed\Algolia\Business\Config\AlgoliaConfigResolverInterface;
use SprykerEco\Zed\Algolia\Business\Filter\ProductConcreteFilterInterface;
use SprykerEco\Zed\Algolia\Business\Filter\ProductDataFilterApplierInterface;
use SprykerEco\Zed\Algolia\Business\Mapper\ProductIndexerInterface;
use SprykerEco\Zed\Algolia\Business\Saver\ProductSaverInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProductExporter implements ProductExporterInterface
{
    /**
     * @var array<string, true>
     */
    protected array $configuredIndices = [];

    public function __construct(
        protected ProductIndexerInterface $algoliaProductIndexer,
        protected ProductSaverInterface $algoliaProductSaver,
        protected ProductConcreteFilterInterface $inactiveProductFilter,
        protected ProductDataFilterApplierInterface $productDataFilterApplier,
        protected AlgoliaConfigResolverInterface $algoliaConfigResolver,
        protected ProductFacadeInterface $productFacade,
        protected SearchClientCreatorInterface $searchClientCreator,
        protected IndexConfiguratorInterface $indexConfigurator
    ) {
    }

    public function exportProducts(
        AlgoliaExportCriteriaTransfer $criteriaTransfer,
        ?OutputInterface $output = null
    ): AlgoliaExportResultTransfer {
        $criteriaTransfer->requireChunkSize();

        $resultTransfer = (new AlgoliaExportResultTransfer())
            ->setEntityType($criteriaTransfer->getEntityType())
            ->setIsSuccessful(true)
            ->setTotalCount(0)
            ->setExportedCount(0)
            ->setFailedCount(0);

        $chunkSize = $criteriaTransfer->getChunkSize();
        $offset = $criteriaTransfer->getOffset() ?? 0;
        $totalProcessed = 0;

        $productConcreteConditionsTransfer = new ProductConcreteConditionsTransfer();
        if ($criteriaTransfer->getLocale()) {
            $productConcreteConditionsTransfer->setLocaleNames([$criteriaTransfer->getLocale()]);
        }

        do {
            $paginationTransfer = (new PaginationTransfer())
                ->setLimit($chunkSize)
                ->setOffset($offset);

            $productConcreteCriteriaTransfer = (new ProductConcreteCriteriaTransfer())
                ->setProductConcreteConditions($productConcreteConditionsTransfer)
                ->setPagination($paginationTransfer)
                ->setWithProductAbstractData(true);

            $productConcreteCollectionTransfer = $this->productFacade->getProductConcreteCollection($productConcreteCriteriaTransfer);

            $productConcreteTransfers = $productConcreteCollectionTransfer->getProducts();
            $productsCount = $productConcreteTransfers->count();

            if ($productsCount > 0) {
                $response = $this->sendProducts($productConcreteTransfers);

                if ($response->getIsSuccessful()) {
                    $resultTransfer->setExportedCount($resultTransfer->getExportedCount() + $productsCount);
                } else {
                    $resultTransfer->setFailedCount($resultTransfer->getFailedCount() + $productsCount);
                }

                $totalProcessed += $productsCount;

                if ($output !== null) {
                    $output->writeln(sprintf(
                        'Totally processed %d products (next offset: %d)',
                        $totalProcessed,
                        $offset + $chunkSize,
                    ));
                }
            }

            $offset += $chunkSize;
        } while ($productsCount === $chunkSize);

        $resultTransfer->setTotalCount($totalProcessed);

        $resultTransfer->addMessage(sprintf(
            '%d of %d product(s) successfully exported to Algolia',
            $resultTransfer->getExportedCount(),
            $resultTransfer->getTotalCount(),
        ));

        return $resultTransfer;
    }

    /**
     * @param \ArrayObject<\Generated\Shared\Transfer\ProductConcreteTransfer> $productConcreteTransfers
     */
    protected function sendProducts(ArrayObject $productConcreteTransfers): AlgoliaResponseTransfer
    {
        $algoliaConfigTransfer = $this->algoliaConfigResolver->getConfig();

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

        $this->configureIndices($indexedAlgoliaProductCollections, $algoliaConfigTransfer);

        return $this->algoliaProductSaver->saveAlgoliaProducts(
            $indexedAlgoliaProductCollections,
            $algoliaConfigTransfer,
        );
    }

    /**
     * @param array<\Generated\Shared\Transfer\IndexedAlgoliaProductCollectionTransfer> $indexedAlgoliaProductCollections
     */
    protected function configureIndices(array $indexedAlgoliaProductCollections, AlgoliaConfigTransfer $algoliaConfigTransfer): void
    {
        $searchClient = $this->searchClientCreator->createSearchClientFromConfig($algoliaConfigTransfer);

        foreach ($indexedAlgoliaProductCollections as $collection) {
            $indexName = $collection->getIndexName();

            if (isset($this->configuredIndices[$indexName])) {
                continue;
            }

            $this->indexConfigurator->configureIndex($indexName, $searchClient, $collection->getLocale());
            $this->configuredIndices[$indexName] = true;
        }
    }
}
