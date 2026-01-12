<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Filter;

use ArrayObject;
use Generated\Shared\Transfer\AlgoliaConfigTransfer;
use Generated\Shared\Transfer\ProductConcreteTransfer;
use Generated\Shared\Transfer\StoreTransfer;

class ProductConcreteFilter implements ProductConcreteFilterInterface
{
    /**
     * @var string
     */
    public const STATUS_APPROVED = 'approved';

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\ProductConcreteTransfer> $productsConcrete

     * @return \ArrayObject<int, \Generated\Shared\Transfer\ProductConcreteTransfer>
     */
    public function filterIndexableProductsConcrete(ArrayObject $productsConcrete, AlgoliaConfigTransfer $algoliaConfigTransfer): ArrayObject
    {
        $filteredProductsConcrete = new ArrayObject();
        foreach ($productsConcrete as $productConcrete) {
            $clonedProductConcrete = clone $productConcrete;
            $clonedProductConcrete->setStores((new ArrayObject()));

            foreach ($productConcrete->getStores() as $storeTransfer) {
                if ($this->canBeIndexed($productConcrete, $storeTransfer, $algoliaConfigTransfer)) {
                    $clonedProductConcrete->addStores($storeTransfer);
                }
            }

            if (count($clonedProductConcrete->getStores())) {
                $filteredProductsConcrete->append($clonedProductConcrete);
            }
        }

        return $filteredProductsConcrete;
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\ProductConcreteTransfer> $productsConcrete

     * @return \ArrayObject<int, \Generated\Shared\Transfer\ProductConcreteTransfer>
     */
    public function filterNonIndexableProductsConcrete(ArrayObject $productsConcrete, AlgoliaConfigTransfer $algoliaConfigTransfer): ArrayObject
    {
        $filteredProductsConcrete = new ArrayObject();
        foreach ($productsConcrete as $productConcrete) {
            $clonedProductConcrete = clone $productConcrete;
            $clonedProductConcrete->setStores((new ArrayObject()));

            foreach ($productConcrete->getStores() as $storeTransfer) {
                if (!$this->canBeIndexed($productConcrete, $storeTransfer, $algoliaConfigTransfer)) {
                    $clonedProductConcrete->addStores($storeTransfer);
                }
            }

            if (count($clonedProductConcrete->getStores()) || count($productConcrete->getStores()) === 0) {
                $filteredProductsConcrete->append($clonedProductConcrete);
            }
        }

        return $filteredProductsConcrete;
    }

    protected function canBeIndexed(
        ProductConcreteTransfer $productConcreteTransfer,
        StoreTransfer $storeTransfer,
        AlgoliaConfigTransfer $algoliaConfigTransfer
    ): bool {
        return $productConcreteTransfer->getIsActive()
            && ($productConcreteTransfer->getApprovalStatus() === null
                || $productConcreteTransfer->getApprovalStatus() === static::STATUS_APPROVED)
            && $this->assertPricesAreValid($productConcreteTransfer, $storeTransfer, $algoliaConfigTransfer);
    }

    protected function assertPricesAreValid(
        ProductConcreteTransfer $productConcreteTransfer,
        StoreTransfer $storeTransfer,
        AlgoliaConfigTransfer $algoliaConfigTransfer
    ): bool {
        if ($algoliaConfigTransfer->getProductsWithoutPrice()) {
            return true;
        }

        foreach ($productConcreteTransfer->getProductAbstractPrices() as $productAbstractPrice) {
            if (
                $productAbstractPrice->getMoneyValue()
                && $productAbstractPrice->getMoneyValue()->getStore()->getName() === $storeTransfer->getName()
                && ($productAbstractPrice->getMoneyValue()->getGrossAmount() !== null || $productAbstractPrice->getMoneyValue()->getNetAmount() !== null)
            ) {
                return true;
            }
        }

        return false;
    }
}
