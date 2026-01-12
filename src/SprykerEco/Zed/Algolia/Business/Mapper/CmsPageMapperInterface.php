<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Algolia\Business\Mapper;

use Generated\Shared\Transfer\CmsPagePublishedTransfer;
use Generated\Shared\Transfer\CmsPageTransfer;

interface CmsPageMapperInterface
{
    /**
     * @param array $flattenedLocaleCmsPageData
     *
     * @return array|null
     */
    public function mapCmsPageDataToAlgoliaData(
        CmsPagePublishedTransfer $cmsPagePublishedTransfer,
        string $locale,
        CmsPageTransfer $cmsPageTransfer,
        array $flattenedLocaleCmsPageData
    ): ?array;
}
