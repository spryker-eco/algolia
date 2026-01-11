<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Filter;

use ArrayObject;

class ProductDataFilterApplier implements ProductDataFilterApplierInterface
{
    /**
     * @var array<\SprykerEco\Zed\Algolia\Business\Filter\ProductDataFilterInterface>
     */
    protected array $productDataFilters;

    /**
     * @param array<\SprykerEco\Zed\Algolia\Business\Filter\ProductDataFilterInterface> $productDataFilters
     */
    public function __construct(array $productDataFilters)
    {
        $this->productDataFilters = $productDataFilters;
    }

    /**
     * @param \ArrayObject<\Generated\Shared\Transfer\ProductConcreteTransfer> $productConcretes
     *
     * @return \ArrayObject<\Generated\Shared\Transfer\ProductConcreteTransfer>
     */
    public function apply(ArrayObject $productConcretes): ArrayObject
    {
        $result = new ArrayObject();

        foreach ($productConcretes as $productConcrete) {
            foreach ($this->productDataFilters as $productDataFilter) {
                $productConcrete = $productDataFilter->filterProductData($productConcrete);
            }

            $result->append($productConcrete);
        }

        return $result;
    }
}
