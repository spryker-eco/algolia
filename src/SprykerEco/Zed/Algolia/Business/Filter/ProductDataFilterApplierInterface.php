<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Filter;

use ArrayObject;

interface ProductDataFilterApplierInterface
{
    /**
     * @param \ArrayObject<\Generated\Shared\Transfer\ProductConcreteTransfer> $productConcretes
     *
     * @return \ArrayObject<\Generated\Shared\Transfer\ProductConcreteTransfer>
     */
    public function apply(ArrayObject $productConcretes): ArrayObject;
}
