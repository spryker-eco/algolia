<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Deleter;

use Generated\Shared\Transfer\AlgoliaConfigTransfer;
use Generated\Shared\Transfer\ProductDeletedTransfer;

interface ProductDeleterInterface
{
    /**
     * @return void
     */
    public function deleteProduct(ProductDeletedTransfer $productDeletedTransfer): void;

    /**
     * @param array<\Generated\Shared\Transfer\ProductDeletedTransfer> $productDeletedTransfers
     * @param string|null $storeName
     *
     * @return void
     */
    public function deleteProducts(array $productDeletedTransfers, AlgoliaConfigTransfer $algoliaConfigTransfer, ?string $storeName = null): void;
}
