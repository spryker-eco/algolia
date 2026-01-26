<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Communication\Plugin\Publisher\CmsPage;

use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\PublisherExtension\Dependency\Plugin\PublisherPluginInterface;

/**
 * Publishes CMS page updates to Algolia.
 *
 * @method \SprykerEco\Zed\Algolia\Business\AlgoliaFacadeInterface getFacade()
 * @method \SprykerEco\Zed\Algolia\AlgoliaConfig getConfig()
 * @method \SprykerEco\Zed\Algolia\Communication\AlgoliaCommunicationFactory getFactory()
 * @method \SprykerEco\Zed\Algolia\Business\AlgoliaBusinessFactory getBusinessFactory()
 */
class AlgoliaCmsPagePublisherPlugin extends AbstractPlugin implements PublisherPluginInterface
{
    /**
     * {@inheritDoc}
     * - Publishes CMS page data to Algolia.
     * - Extracts CMS page IDs from event entity transfers.
     * - Fetches CMS page data using CmsFacade.
     * - Determines if page should be published based on isActive and isSearchable flags.
     * - Sends CMS page data to Algolia for indexing.
     *
     * @api
     *
     * @param array<\Generated\Shared\Transfer\EventEntityTransfer> $eventEntityTransfers
     * @param string $eventName
     */
    public function handleBulk(array $eventEntityTransfers, $eventName): void
    {
        $cmsPageIds = $this->getFactory()->getEventBehaviorFacade()->getEventTransferIds($eventEntityTransfers);

        if (!$cmsPageIds) {
            return;
        }

        $this->processCmsPages($cmsPageIds);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return array<string>
     */
    public function getSubscribedEvents(): array
    {
        if (!$this->getConfig()->getIsActive()) {
            return [];
        }

        return $this->getConfig()->getCmsPageUpdateSubscribedEvents();
    }

    /**
     * @param array<int> $cmsPageIds
     */
    protected function processCmsPages(array $cmsPageIds): void
    {
        $cmsFacade = $this->getFactory()->getCmsFacade();

        foreach ($cmsPageIds as $cmsPageId) {
            $cmsPageTransfer = $cmsFacade->findCmsPageById($cmsPageId);

            if ($cmsPageTransfer === null) {
                continue;
            }

            if (!$cmsPageTransfer->getIsActive() || !$cmsPageTransfer->getIsSearchable()) {
                $this->getFacade()->deleteCmsPages([$cmsPageId]);

                continue;
            }

            $cmsVersionTransfer = $cmsFacade->findLatestCmsVersionByIdCmsPage($cmsPageId);
            if ($cmsVersionTransfer === null) {
                continue;
            }

            $cmsPagePublishedTransfer = $this->getBusinessFactory()->createCmsPagePublishedTransferBuilder()
                ->buildCmsPagePublishedTransfer(
                    $cmsPageTransfer,
                    $cmsVersionTransfer,
                );

            $this->getFacade()->publishCmsPage($cmsPagePublishedTransfer);
        }
    }
}
