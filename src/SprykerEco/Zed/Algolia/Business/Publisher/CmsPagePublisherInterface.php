<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Publisher;

use Generated\Shared\Transfer\AlgoliaResponseTransfer;
use Generated\Shared\Transfer\CmsPagePublishedTransfer;

interface CmsPagePublisherInterface
{
    /**
     * @param \Generated\Shared\Transfer\CmsPagePublishedTransfer $cmsPagePublishedTransfer
     *
     * @return \Generated\Shared\Transfer\AlgoliaResponseTransfer
     */
    public function publishCmsPages(CmsPagePublishedTransfer $cmsPagePublishedTransfer): AlgoliaResponseTransfer;
}
