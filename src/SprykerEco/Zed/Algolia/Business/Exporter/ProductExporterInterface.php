<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Exporter;

use ArrayObject;
use Generated\Shared\Transfer\AlgoliaResponseTransfer;

interface ProductExporterInterface
{
    /**
     * @param \ArrayObject<\Generated\Shared\Transfer\ProductConcreteTransfer> $productConcreteTransfers
     *
     * @return \Generated\Shared\Transfer\AlgoliaResponseTransfer
     */
    public function exportProducts(ArrayObject $productConcreteTransfers): AlgoliaResponseTransfer;
}
