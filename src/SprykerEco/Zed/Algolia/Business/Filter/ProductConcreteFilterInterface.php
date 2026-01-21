<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Filter;

use ArrayObject;
use Generated\Shared\Transfer\AlgoliaConfigTransfer;

interface ProductConcreteFilterInterface
{
    /**
     * @param \ArrayObject<\Generated\Shared\Transfer\ProductConcreteTransfer> $productsConcrete

     * @return \ArrayObject<\Generated\Shared\Transfer\ProductConcreteTransfer>
     */
    public function filterIndexableProductsConcrete(ArrayObject $productsConcrete, AlgoliaConfigTransfer $algoliaConfigTransfer): ArrayObject;

    /**
     * @param \ArrayObject<\Generated\Shared\Transfer\ProductConcreteTransfer> $productsConcrete

     * @return \ArrayObject<\Generated\Shared\Transfer\ProductConcreteTransfer>
     */
    public function filterNonIndexableProductsConcrete(ArrayObject $productsConcrete, AlgoliaConfigTransfer $algoliaConfigTransfer): ArrayObject;
}
