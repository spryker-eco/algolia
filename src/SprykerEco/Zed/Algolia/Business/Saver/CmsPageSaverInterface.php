<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Saver;

use Generated\Shared\Transfer\AlgoliaConfigTransfer;
use Generated\Shared\Transfer\AlgoliaResponseTransfer;

interface CmsPageSaverInterface
{
    public function saveCmsPage(array $indexData, AlgoliaConfigTransfer $algoliaConfigTransfer): AlgoliaResponseTransfer;
}
