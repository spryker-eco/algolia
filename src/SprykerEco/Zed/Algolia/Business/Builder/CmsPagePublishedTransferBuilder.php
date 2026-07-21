<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Builder;

use Generated\Shared\Transfer\CmsPagePublishedTransfer;
use Generated\Shared\Transfer\CmsPageTransfer;
use Generated\Shared\Transfer\CmsVersionTransfer;
use Generated\Shared\Transfer\LocaleTransfer;
use Spryker\Zed\Cms\Business\CmsFacadeInterface;

class CmsPagePublishedTransferBuilder implements CmsPagePublishedTransferBuilderInterface
{
    public function __construct(protected CmsFacadeInterface $cmsFacade)
    {
    }

    public function buildCmsPagePublishedTransfer(
        CmsPageTransfer $cmsPageTransfer,
        CmsVersionTransfer $cmsVersionTransfer
    ): CmsPagePublishedTransfer {
        $cmsPagePublishedTransfer = new CmsPagePublishedTransfer();
        $cmsPagePublishedTransfer->setId($cmsPageTransfer->getFkPage());
        $cmsPagePublishedTransfer->setCmsPage($cmsPageTransfer);
        $cmsPagePublishedTransfer->setCreatedAt($this->getCmsPageCreatedAt($cmsPageTransfer->getFkPage()));
        $cmsPagePublishedTransfer->setUpdatedAt($cmsVersionTransfer->getCreatedAt());
        $cmsPagePublishedTransfer->setFlattenedLocaleCmsPageDatum($this->getFlattenedLocaleCmsPageDatum($cmsPageTransfer));

        return $cmsPagePublishedTransfer;
    }

    protected function getCmsPageCreatedAt(int $idCmsPage): ?string
    {
        $firstCmsVersionTransfer = $this->cmsFacade->findCmsVersionByIdCmsPageAndVersion($idCmsPage, 1);
        if ($firstCmsVersionTransfer === null) {
            return null;
        }

        return $firstCmsVersionTransfer->getCreatedAt();
    }

    /**
     * @return array<string, array>
     */
    protected function getFlattenedLocaleCmsPageDatum(CmsPageTransfer $cmsPageTransfer): array
    {
        $localeCmsPageData = [];
        $cmsVersionDataTransfer = $this->cmsFacade->getCmsVersionData($cmsPageTransfer->getFkPage());

        foreach ($cmsPageTransfer->getPageAttributes() as $pageAttribute) {
            $localeName = $pageAttribute->getLocaleName();
            if ($localeName === null) {
                continue;
            }

            $localeTransfer = (new LocaleTransfer())
            ->setLocaleName($pageAttribute->getLocaleName())
            ->setIdLocale($pageAttribute->getFkLocale());

            $localeCmsPageDataTransfer = $this->cmsFacade->extractLocaleCmsPageDataTransfer($cmsVersionDataTransfer, $localeTransfer);
            $flattenedLocaleCmsPageData = $this->cmsFacade->calculateFlattenedLocaleCmsPageData($localeCmsPageDataTransfer, $localeTransfer);

            $localeCmsPageData[$localeName] = $flattenedLocaleCmsPageData;
        }

        return $localeCmsPageData;
    }
}
