<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Filter;

use Generated\Shared\Transfer\ProductConcreteTransfer;

interface ProductDataFilterInterface
{
    public function filterProductData(
        ProductConcreteTransfer $productConcreteTransfer
    ): ProductConcreteTransfer;
}
