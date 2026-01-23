<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Communication\Plugin\Publisher\Product;

use ArrayObject;
use Generated\Shared\Transfer\ProductConcreteConditionsTransfer;
use Generated\Shared\Transfer\ProductConcreteCriteriaTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\PublisherExtension\Dependency\Plugin\PublisherPluginInterface;

/**
 * @method \SprykerEco\Zed\Algolia\Business\AlgoliaFacadeInterface getFacade()
 * @method \SprykerEco\Zed\Algolia\AlgoliaConfig getConfig()
 * @method \SprykerEco\Zed\Algolia\Communication\AlgoliaCommunicationFactory getFactory()
 */
abstract class AbstractAlgoliaProductPublisherPlugin extends AbstractPlugin implements PublisherPluginInterface
{
    /**
     * @var int
     */
    protected const CHUNK_SIZE = 100;

    /**
     * @param array<\Generated\Shared\Transfer\EventEntityTransfer> $eventEntityTransfers

     * @return \ArrayObject<\Generated\Shared\Transfer\ProductConcreteTransfer>
     */
    protected function getProductConcreteTransfersByEventEntityTransfers(
        array $eventEntityTransfers,
        string $idFieldName = 'id_product'
    ): ArrayObject {
        $productIds = $this->extractProductIdsFromEventEntityTransfers($eventEntityTransfers, $idFieldName);

        if (!$productIds) {
            return new ArrayObject();
        }

        return $this->getProductConcretesByIds($productIds);
    }

    /**
     * @param array<\Generated\Shared\Transfer\EventEntityTransfer> $eventEntityTransfers

     * @return array<int>
     */
    protected function extractProductIdsFromEventEntityTransfers(
        array $eventEntityTransfers,
        string $idFieldName = 'id_product'
    ): array {
        $productIds = [];

        foreach ($eventEntityTransfers as $eventEntityTransfer) {
            $productId = $eventEntityTransfer->getId();
            if ($productId !== null) {
                $productIds[] = $productId;

                continue;
            }

            $foreignKeys = $eventEntityTransfer->getForeignKeys();
            if (isset($foreignKeys[$idFieldName])) {
                $productIds[] = $foreignKeys[$idFieldName];
            }
        }

        return array_unique($productIds);
    }

    /**
     * @param array<int> $productIds
     *
     * @return \ArrayObject<\Generated\Shared\Transfer\ProductConcreteTransfer>
     */
    protected function getProductConcretesByIds(array $productIds): ArrayObject
    {
        // Convert product IDs to SKUs
        $skus = $this->getFactory()
            ->getProductFacade()
            ->getProductConcreteSkusByConcreteIds($productIds);

        if (!$skus) {
            return new ArrayObject();
        }

        // Fetch full product data with abstract data using getProductConcreteCollection
        $productConcreteConditionsTransfer = (new ProductConcreteConditionsTransfer())
            ->setSkus($skus);

        $productConcreteCriteriaTransfer = (new ProductConcreteCriteriaTransfer())
            ->setProductConcreteConditions($productConcreteConditionsTransfer)
            ->setWithProductAbstractData(true);

        $productConcreteCollectionTransfer = $this->getFactory()
            ->getProductFacade()
            ->getProductConcreteCollection($productConcreteCriteriaTransfer);

        return $productConcreteCollectionTransfer->getProducts();
    }

    /**
     * @param array<\Generated\Shared\Transfer\EventEntityTransfer> $eventEntityTransfers
     *
     * @return array<int>
     */
    protected function extractProductAbstractIds(array $eventEntityTransfers): array
    {
        $productAbstractIds = [];

        foreach ($eventEntityTransfers as $eventEntityTransfer) {
            $foreignKeys = $eventEntityTransfer->getForeignKeys();

            if (isset($foreignKeys['fk_product_abstract'])) {
                $productAbstractIds[] = $foreignKeys['fk_product_abstract'];
            }
        }

        return array_unique($productAbstractIds);
    }

    /**
     * @param array<int> $productAbstractIds
     *
     * @return \ArrayObject<\Generated\Shared\Transfer\ProductConcreteTransfer>
     */
    protected function getProductConcretesByAbstractIds(array $productAbstractIds): ArrayObject
    {
        // Get all product IDs for these abstract IDs
        $productIds = [];
        foreach ($productAbstractIds as $productAbstractId) {
            $concreteIds = $this->getFactory()
                ->getProductFacade()
                ->findProductConcreteIdsByAbstractProductId($productAbstractId);
            $productIds = array_merge($productIds, $concreteIds);
        }

        if (!$productIds) {
            return new ArrayObject();
        }

        // Convert product IDs to SKUs
        $skus = $this->getFactory()
            ->getProductFacade()
            ->getProductConcreteSkusByConcreteIds($productIds);

        if (!$skus) {
            return new ArrayObject();
        }

        // Fetch full product data with abstract data using getProductConcreteCollection
        $productConcreteConditionsTransfer = (new ProductConcreteConditionsTransfer())
            ->setSkus($skus);

        $productConcreteCriteriaTransfer = (new ProductConcreteCriteriaTransfer())
            ->setProductConcreteConditions($productConcreteConditionsTransfer)
            ->setWithProductAbstractData(true);

        $productConcreteCollectionTransfer = $this->getFactory()
            ->getProductFacade()
            ->getProductConcreteCollection($productConcreteCriteriaTransfer);

        return $productConcreteCollectionTransfer->getProducts();
    }
}
