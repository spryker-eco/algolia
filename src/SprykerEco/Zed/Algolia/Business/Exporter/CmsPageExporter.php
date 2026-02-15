<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Exporter;

use Generated\Shared\Transfer\AlgoliaExportCriteriaTransfer;
use Generated\Shared\Transfer\AlgoliaExportResultTransfer;
use Generator;
use Orm\Zed\Cms\Persistence\Map\SpyCmsPageTableMap;
use Spryker\Zed\Cms\Business\CmsFacadeInterface;
use Spryker\Zed\Cms\Persistence\CmsQueryContainerInterface;
use SprykerEco\Zed\Algolia\Business\Builder\CmsPagePublishedTransferBuilderInterface;
use SprykerEco\Zed\Algolia\Business\Publisher\CmsPagePublisherInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CmsPageExporter implements CmsPageExporterInterface
{
    public function __construct(
        protected CmsPagePublisherInterface $cmsPagePublisher,
        protected CmsFacadeInterface $cmsFacade,
        protected CmsQueryContainerInterface $cmsQueryContainer,
        protected CmsPagePublishedTransferBuilderInterface $cmsPagePublishedTransferBuilder
    ) {
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface|null $output
     */
    public function exportCmsPages(
        AlgoliaExportCriteriaTransfer $criteriaTransfer,
        ?OutputInterface $output = null
    ): AlgoliaExportResultTransfer {
        $criteriaTransfer->requireChunkSize();

        $resultTransfer = (new AlgoliaExportResultTransfer())
        ->setEntityType($criteriaTransfer->getEntityType())
        ->setIsSuccessful(true)
        ->setTotalCount(0)
        ->setExportedCount(0)
        ->setFailedCount(0);

        $query = $this->createCmsPageQuery($criteriaTransfer);
        $totalCount = $query->count();
        $resultTransfer->setTotalCount($totalCount);

        if ($totalCount === 0) {
            $resultTransfer->addMessage('No CMS pages found matching the criteria');

            return $resultTransfer;
        }

        $offset = 0;

        foreach ($this->getCmsPageIdChunks($criteriaTransfer) as $cmsPageIds) {
            $processedInChunk = 0;

            foreach ($cmsPageIds as $cmsPageId) {
                $cmsPageTransfer = $this->cmsFacade->findCmsPageById($cmsPageId);
                if ($cmsPageTransfer === null) {
                    $resultTransfer->setFailedCount($resultTransfer->getFailedCount() + 1);

                    continue;
                }

                if ($cmsPageTransfer->getIsActive() && $cmsPageTransfer->getIsSearchable()) {
                    $cmsVersionTransfer = $this->cmsFacade->findLatestCmsVersionByIdCmsPage($cmsPageTransfer->getFkPage());
                    if ($cmsVersionTransfer === null) {
                        $resultTransfer->setFailedCount($resultTransfer->getFailedCount() + 1);

                        continue;
                    }

                    $cmsPagePublishedTransfer = $this->cmsPagePublishedTransferBuilder->buildCmsPagePublishedTransfer(
                        $cmsPageTransfer,
                        $cmsVersionTransfer,
                    );
                    $algoliaResponseTransfer = $this->cmsPagePublisher->publishCmsPage($cmsPagePublishedTransfer);

                    if ($algoliaResponseTransfer->getIsSuccessful()) {
                        $resultTransfer->setExportedCount($resultTransfer->getExportedCount() + 1);
                        $processedInChunk++;
                    } else {
                        $resultTransfer->setFailedCount($resultTransfer->getFailedCount() + 1);
                    }
                }
            }

            if ($output !== null && $processedInChunk > 0) {
                $output->writeln(sprintf(
                    'Processed %d CMS page(s) (offset: %d)',
                    $processedInChunk,
                    $offset,
                ));
            }

            $offset += $criteriaTransfer->getChunkSize();
        }

        if ($resultTransfer->getFailedCount() > 0) {
            $resultTransfer
            ->setIsSuccessful(false)
            ->addMessage(sprintf(
                '%d CMS page(s) failed to export',
                $resultTransfer->getFailedCount(),
            ));
        }

        $resultTransfer->addMessage(sprintf(
            '%d of %d CMS page(s) successfully exported to Algolia',
            $resultTransfer->getExportedCount(),
            $resultTransfer->getTotalCount(),
        ));

        return $resultTransfer;
    }

    /**
     * @return \Orm\Zed\Cms\Persistence\SpyCmsPageQuery
     */
    protected function createCmsPageQuery(AlgoliaExportCriteriaTransfer $criteriaTransfer)
    {
        $query = $this->cmsQueryContainer->queryPages();

        if ($criteriaTransfer->getStoreName()) {
            $query
            ->useSpyCmsPageStoreQuery()
                ->joinWithSpyStore()
                ->useSpyStoreQuery()
                    ->filterByName($criteriaTransfer->getStoreName())
                ->endUse()
            ->endUse();
        }

        return $query;
    }

    /**
     * @return \Generator<array<int>>
     */
    protected function getCmsPageIdChunks(AlgoliaExportCriteriaTransfer $criteriaTransfer): Generator
    {
        $chunkSize = $criteriaTransfer->getChunkSize();
        $offset = 0;

        do {
            $query = $this->createCmsPageQuery($criteriaTransfer)
            ->select([SpyCmsPageTableMap::COL_ID_CMS_PAGE])
            ->limit($chunkSize)
            ->offset($offset);

            // Ensure distinct results when joining with store relation
            if ($criteriaTransfer->getStoreName()) {
                $query->distinct();
            }

            $cmsPageIds = $query->find()->getData();

            if (count($cmsPageIds) > 0) {
                yield $cmsPageIds;
            }

            $offset += $chunkSize;
        } while (count($cmsPageIds) === $chunkSize);
    }
}
