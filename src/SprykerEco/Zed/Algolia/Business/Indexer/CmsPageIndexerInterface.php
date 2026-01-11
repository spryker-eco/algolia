<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Indexer;

use Generated\Shared\Transfer\CmsPagePublishedTransfer;
use Generated\Shared\Transfer\CmsPageTransfer;

interface CmsPageIndexerInterface
{
 /**
  * @param \Generated\Shared\Transfer\CmsPagePublishedTransfer $cmsPagePublishedTransfer
  * @param string $locale
  * @param string $tenantIdentifier
  * @param \Generated\Shared\Transfer\CmsPageTransfer $cmsPageTransfer
  * @param array $flattenedLocaleCmsPageData
  *
  * @return array
  */
    public function buildIndexData(
        CmsPagePublishedTransfer $cmsPagePublishedTransfer,
        string $locale,
        string $tenantIdentifier,
        CmsPageTransfer $cmsPageTransfer,
        array $flattenedLocaleCmsPageData
    ): array;
}
