<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Saver;

use Generated\Shared\Transfer\AlgoliaConfigTransfer;
use Generated\Shared\Transfer\AlgoliaResponseTransfer;

interface ProductSaverInterface
{
    /**
     * @param array<\Generated\Shared\Transfer\IndexedAlgoliaProductCollectionTransfer> $indexedAlgoliaProductCollections
     * @param \Generated\Shared\Transfer\AlgoliaConfigTransfer $algoliaConfigTransfer
     *
     * @return \Generated\Shared\Transfer\AlgoliaResponseTransfer
     */
    public function saveAlgoliaProducts(array $indexedAlgoliaProductCollections, AlgoliaConfigTransfer $algoliaConfigTransfer): AlgoliaResponseTransfer;
}
