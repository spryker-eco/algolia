<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Publisher;

use Generated\Shared\Transfer\AlgoliaResponseTransfer;
use Generated\Shared\Transfer\CmsPagePublishedTransfer;
use SprykerEco\Zed\Algolia\Business\Indexer\CmsPageIndexerInterface;
use SprykerEco\Zed\Algolia\Business\Resolver\AlgoliaConfigResolver;
use SprykerEco\Zed\Algolia\Business\Saver\CmsPageSaverInterface;

class CmsPagePublisher implements CmsPagePublisherInterface
{
    /**
     * @param \SprykerEco\Zed\Algolia\Business\Indexer\CmsPageIndexerInterface $cmsPageIndexer
     * @param \SprykerEco\Zed\Algolia\Business\Saver\CmsPageSaverInterface $cmsPageSaver
     * @param \SprykerEco\Zed\Algolia\Business\Resolver\AlgoliaConfigResolver $algoliaConfigResolver
     */
    public function __construct(
        protected CmsPageIndexerInterface $cmsPageIndexer,
        protected CmsPageSaverInterface $cmsPageSaver,
        protected AlgoliaConfigResolver $algoliaConfigResolver
    ) {
    }

    /**
     * @param \Generated\Shared\Transfer\CmsPagePublishedTransfer $cmsPagePublishedTransfer
     *
     * @return \Generated\Shared\Transfer\AlgoliaResponseTransfer
     */
    public function publishCmsPages(CmsPagePublishedTransfer $cmsPagePublishedTransfer): AlgoliaResponseTransfer
    {
        $algoliaConfigTransfer = $this->algoliaConfigResolver->findConfig();
        if ($algoliaConfigTransfer === null) {
            return $this->createSuccessResponse();
        }
        $cmsPageTransfer = $cmsPagePublishedTransfer->getCmsPage();
        $flattenedLocaleCmsPageDatum = $cmsPagePublishedTransfer->getFlattenedLocaleCmsPageDatum();
        if (!$cmsPageTransfer->getIsActive() || !$cmsPageTransfer->getIsSearchable()) {
            return $this->createSuccessResponse();
        }

        foreach ($cmsPageTransfer->getPageAttributes() as $pageAttribute) {
            $locale = $pageAttribute->getLocaleName();
            if ($locale === null) {
                continue;
            }
            $flattenedLocaleCmsPageData = $flattenedLocaleCmsPageDatum[$locale] ?? [];
            $indexData = $this->cmsPageIndexer->buildIndexData($cmsPagePublishedTransfer, $locale, $algoliaConfigTransfer->getTenantIdentifier(), $cmsPageTransfer, $flattenedLocaleCmsPageData);

            if ($indexData) {
                $this->cmsPageSaver->saveCmsPage($indexData, $algoliaConfigTransfer);
            }
        }

        return $this->createSuccessResponse();
    }

    /**
     * @return \Generated\Shared\Transfer\AlgoliaResponseTransfer
     */
    protected function createSuccessResponse(): AlgoliaResponseTransfer
    {
        return (new AlgoliaResponseTransfer())->setIsSuccessful(true);
    }
}
