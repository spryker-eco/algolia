<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Deleter;

use Generated\Shared\Transfer\AlgoliaConfigTransfer;
use Generated\Shared\Transfer\CmsPageUnpublishedTransfer;

interface CmsPageDeleterInterface
{
    /**
     * @return void
     */
    public function deleteCmsPage(CmsPageUnpublishedTransfer $cmsPageUnpublishedTransfer): void;

    /**
     * @param array<\Generated\Shared\Transfer\CmsPageUnpublishedTransfer> $cmsPageUnpublishedTransfers
     * @param string|null $storeName
     *
     * @return void
     */
    public function deleteCmsPages(array $cmsPageUnpublishedTransfers, AlgoliaConfigTransfer $algoliaConfigTransfer, ?string $storeName = null): void;
}
