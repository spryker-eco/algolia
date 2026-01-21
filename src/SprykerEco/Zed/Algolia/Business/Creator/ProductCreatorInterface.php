<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Creator;

use ArrayObject;
use Generated\Shared\Transfer\AlgoliaResponseTransfer;

interface ProductCreatorInterface
{
    /**
     * @param \ArrayObject<\Generated\Shared\Transfer\ProductConcreteTransfer> $productConcreteTransfers
     *
     * @return \Generated\Shared\Transfer\AlgoliaResponseTransfer
     */
    public function createProducts(ArrayObject $productConcreteTransfers): AlgoliaResponseTransfer;
}
