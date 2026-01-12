<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Filter;

use ArrayObject;
use Generated\Shared\Transfer\PriceProductTransfer;
use Generated\Shared\Transfer\ProductConcreteTransfer;

class PriceProductDataFilter implements ProductDataFilterInterface
{
    /**
     * Price dimension name default
     *
     * @var string
     */
    protected const PRICE_DIMENSION_DEFAULT_NAME = 'Default';

    /**
     * Price type default
     *
     * @var string
     */
    protected const PRICE_TYPE_DEFAULT = 'DEFAULT';

    public function filterProductData(
        ProductConcreteTransfer $productConcreteTransfer
    ): ProductConcreteTransfer {
        $storeNames = [];

        foreach ($productConcreteTransfer->getStores() as $storeTransfer) {
            $storeNames[] = $storeTransfer->getName();
        }

        return $productConcreteTransfer
            ->setProductAbstractPrices(
                $this->filterPriceProductsByStores(
                    $productConcreteTransfer->getProductAbstractPrices(),
                    $storeNames,
                ),
            )
            ->setPrices(
                $this->filterPriceProductsByStores(
                    $productConcreteTransfer->getProductAbstractPrices(),
                    $storeNames,
                ),
            );
    }

    /**
     * @param \ArrayObject<\Generated\Shared\Transfer\PriceProductTransfer> $priceProductTransfers
     * @param array $storeNames
     *
     * @return \ArrayObject<\Generated\Shared\Transfer\PriceProductTransfer>
     */
    protected function filterPriceProductsByStores(
        ArrayObject $priceProductTransfers,
        array $storeNames
    ): ArrayObject {
        $filteredPriceProductTransfers = new ArrayObject();

        foreach ($priceProductTransfers as $priceProductTransfer) {
            if ($this->isPriceProductApplicable($priceProductTransfer, $storeNames)) {
                $filteredPriceProductTransfers->append($priceProductTransfer);
            }
        }

        return $filteredPriceProductTransfers;
    }

    /**
     * @param list<string> $storeNames
     */
    protected function isPriceProductApplicable(PriceProductTransfer $priceProductTransfer, array $storeNames): bool
    {
        return in_array($priceProductTransfer->getMoneyValue()->getStore()->getName(), $storeNames, true)
            && $priceProductTransfer->getPriceDimension()->getName() === static::PRICE_DIMENSION_DEFAULT_NAME
            && $priceProductTransfer->getPriceType()->getName() === static::PRICE_TYPE_DEFAULT
            && !$priceProductTransfer->getVolumeQuantity();
    }
}
