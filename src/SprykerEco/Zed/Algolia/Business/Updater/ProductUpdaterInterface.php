<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Updater;

use Generated\Shared\Transfer\AlgoliaResponseTransfer;
use Generated\Shared\Transfer\ProductUpdatedTransfer;

interface ProductUpdaterInterface
{
    /**
     * @return \Generated\Shared\Transfer\AlgoliaResponseTransfer
     */
    public function updateProducts(ProductUpdatedTransfer $productUpdatedTransfer): AlgoliaResponseTransfer;
}
