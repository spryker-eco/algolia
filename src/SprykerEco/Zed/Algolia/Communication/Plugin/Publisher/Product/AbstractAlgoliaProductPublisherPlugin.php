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
    protected const string KEY_FK_PRODUCT_ABSTRACT = 'fk_product_abstract';

    protected const string KEY_FK_RESOURCE_PRODUCT_ABSTRACT = 'fk_resource_product_abstract';

    protected const string KEY_FK_PRODUCT = 'fk_product';

    /**
     * @param array<\Generated\Shared\Transfer\EventEntityTransfer> $eventEntityTransfers

     * @return \ArrayObject<\Generated\Shared\Transfer\ProductConcreteTransfer>
     */
    protected function getProductConcreteTransfersByEventEntityTransfers(
        array $eventEntityTransfers
    ): ArrayObject {
        $productIds = $this->extractProductIdsFromEventEntityTransfers($eventEntityTransfers);

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
        array $eventEntityTransfers
    ): array {
        $productIds = [];
        $fkProductIds = [];

        foreach ($eventEntityTransfers as $eventEntityTransfer) {
            $foreignKeys = $eventEntityTransfer->getForeignKeys();

            // checking if event has foreign key for product, the format is {table_name}.fk_product
            $key = sprintf('%s.%s', $eventEntityTransfer->getName(), static::KEY_FK_PRODUCT);
            if (!empty($foreignKeys[$key])) {
                $fkProductIds[$foreignKeys[$key]] = $foreignKeys[$key];

                continue;
            }

            if ($eventEntityTransfer->getId() !== null) {
                $productIds[] = $eventEntityTransfer->getId();
            }
        }

        return array_unique(array_merge($productIds, $fkProductIds));
    }

    /**
     * @param array<int> $productIds
     *
     * @return \ArrayObject<\Generated\Shared\Transfer\ProductConcreteTransfer>
     */
    protected function getProductConcretesByIds(array $productIds): ArrayObject
    {
        $productConcreteConditionsTransfer = (new ProductConcreteConditionsTransfer())
            ->setIds($productIds);

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
        $fkProductAbstractIds = [];

        foreach ($eventEntityTransfers as $eventEntityTransfer) {
            // checking if event has foreign key for product abstract, the format is {table_name}.fk_product_abstract
            $foreignKeys = $eventEntityTransfer->getForeignKeys();
            $key = sprintf('%s.%s', $eventEntityTransfer->getName(), static::KEY_FK_PRODUCT_ABSTRACT);
            if (!empty($foreignKeys[$key])) {
                $fkProductAbstractIds[$foreignKeys[$key]] = $foreignKeys[$key];

                continue;
            }

            // for URLs events
            $key = sprintf('%s.%s', $eventEntityTransfer->getName(), static::KEY_FK_RESOURCE_PRODUCT_ABSTRACT);
            if (!empty($foreignKeys[$key])) {
                $fkProductAbstractIds[$foreignKeys[$key]] = $foreignKeys[$key];

                continue;
            }

            if ($eventEntityTransfer->getId() !== null) {
                $productAbstractIds[] = $eventEntityTransfer->getId();
            }
        }

        return array_unique(array_merge($productAbstractIds, $fkProductAbstractIds));
    }

    /**
     * @param array<int> $productAbstractIds
     *
     * @return \ArrayObject<\Generated\Shared\Transfer\ProductConcreteTransfer>
     */
    protected function getProductConcretesByAbstractIds(array $productAbstractIds): ArrayObject
    {
        $productConcreteConditionsTransfer = (new ProductConcreteConditionsTransfer())
            ->setProductAbstractIds($productAbstractIds);

        $productConcreteCriteriaTransfer = (new ProductConcreteCriteriaTransfer())
            ->setProductConcreteConditions($productConcreteConditionsTransfer)
            ->setWithProductAbstractData(true);

        $productConcreteCollectionTransfer = $this->getFactory()
            ->getProductFacade()
            ->getProductConcreteCollection($productConcreteCriteriaTransfer);

        return $productConcreteCollectionTransfer->getProducts();
    }
}
