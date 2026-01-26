<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Builder;

use Generated\Shared\Transfer\CmsPagePublishedTransfer;
use Generated\Shared\Transfer\CmsPageTransfer;
use Generated\Shared\Transfer\CmsVersionTransfer;

interface CmsPagePublishedTransferBuilderInterface
{
    /**
     * @return \Generated\Shared\Transfer\CmsPagePublishedTransfer
     */
    public function buildCmsPagePublishedTransfer(
        CmsPageTransfer $cmsPageTransfer,
        CmsVersionTransfer $cmsVersionTransfer
    ): CmsPagePublishedTransfer;
}
