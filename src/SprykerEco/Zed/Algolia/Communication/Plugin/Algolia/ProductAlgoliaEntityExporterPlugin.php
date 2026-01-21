<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Communication\Plugin\Algolia;

use Exception;
use Generated\Shared\Transfer\AlgoliaExportCriteriaTransfer;
use Generated\Shared\Transfer\AlgoliaExportResultTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use Generated\Shared\Transfer\ProductConcreteConditionsTransfer;
use Generated\Shared\Transfer\ProductConcreteCriteriaTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use SprykerEco\Zed\Algolia\Dependency\Plugin\AlgoliaEntityExporterPluginInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @method \SprykerEco\Zed\Algolia\AlgoliaConfig getConfig()
 * @method \SprykerEco\Zed\Algolia\Business\AlgoliaFacadeInterface getFacade()
 * @method \SprykerEco\Zed\Algolia\Communication\AlgoliaCommunicationFactory getFactory()
 * @method \SprykerEco\Zed\Algolia\Business\AlgoliaBusinessFactory getBusinessFactory()
 */
class ProductAlgoliaEntityExporterPlugin extends AbstractPlugin implements AlgoliaEntityExporterPluginInterface
{
    /**
     * @var string
     */
    protected const ENTITY_TYPE = 'product';

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return string
     */
    public function getEntityType(): string
    {
        return static::ENTITY_TYPE;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\AlgoliaExportCriteriaTransfer $criteriaTransfer
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return \Generated\Shared\Transfer\AlgoliaExportResultTransfer
     */
    public function export(
        AlgoliaExportCriteriaTransfer $criteriaTransfer,
        OutputInterface $output
    ): AlgoliaExportResultTransfer {
        $resultTransfer = (new AlgoliaExportResultTransfer())
            ->setEntityType(static::ENTITY_TYPE)
            ->setIsSuccessful(true);

        try {
            if ($criteriaTransfer->getIsDryRun()) {
                $output->writeln('<comment>DRY RUN: Would trigger product export events</comment>');
                $resultTransfer->addMessage('Dry run completed - no actual export performed');

                return $resultTransfer;
            }

            $output->writeln('Publishing products to the Algolia queue...');

            $chunkSize = $criteriaTransfer->getChunkSize(); // Default chunk size is configured in AlgoliaConfig::getDefaultExportChunkSize().
            $offset = 0;
            $totalExported = 0;
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
                    ->setPagination($paginationTransfer);

                $productConcreteCollectionTransfer = $this->getBusinessFactory()
                    ->getProductFacade()
                    ->getProductConcreteCollection($productConcreteCriteriaTransfer);

                $productConcreteTransfers = $productConcreteCollectionTransfer->getProducts();
                $productsCount = $productConcreteTransfers->count();

                if ($productsCount > 0) {
                    $response = $this->getFacade()->exportProducts($productConcreteTransfers);
                    if ($response->getIsSuccessful()) {
                        $totalExported += $productsCount;
                    }

                    $totalProcessed += $productsCount;

                    $output->writeln(sprintf(
                        'Processed %d products (offset: %d)',
                        $productsCount,
                        $offset,
                    ));
                }

                $offset += $chunkSize;
            } while ($productsCount === $chunkSize);

            $resultTransfer
                ->addMessage(sprintf('%d products are sent to Algolia.', $totalProcessed))
                ->setTotalCount($totalProcessed)
                ->setExportedCount($totalExported);

            if ($totalExported !== $totalProcessed) {
                $resultTransfer
                    ->setIsSuccessful(false)
                    ->addMessage('Error: some products are not exported successfully.');
            }
        } catch (Exception $exception) {
            $resultTransfer
                ->setIsSuccessful(false)
                ->addMessage(sprintf('Error: %s', $exception->getMessage()));
            $output->writeln(sprintf('<error>%s</error>', $exception->getMessage()));
        }

        return $resultTransfer;
    }
}
