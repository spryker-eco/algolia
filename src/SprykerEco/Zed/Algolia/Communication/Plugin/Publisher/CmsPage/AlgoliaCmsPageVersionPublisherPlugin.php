<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Communication\Plugin\Publisher\CmsPage;

use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\PublisherExtension\Dependency\Plugin\PublisherPluginInterface;

/**
 * Publishes CMS page versions to Algolia when a new version is created/published.
 *
 * @method \SprykerEco\Zed\Algolia\Business\AlgoliaFacadeInterface getFacade()
 * @method \SprykerEco\Zed\Algolia\AlgoliaConfig getConfig()
 * @method \SprykerEco\Zed\Algolia\Communication\AlgoliaCommunicationFactory getFactory()
 * @method \SprykerEco\Zed\Algolia\Business\AlgoliaBusinessFactory getBusinessFactory()
 */
class AlgoliaCmsPageVersionPublisherPlugin extends AbstractPlugin implements PublisherPluginInterface
{
    /**
     * {@inheritDoc}
     * - Publishes CMS page versions to Algolia.
     * - Extracts CMS version IDs from event entity transfers.
     * - Maps version IDs to CMS page IDs.
     * - Fetches CMS page data using CmsFacade.
     * - Sends CMS page data to Algolia for indexing.
     *
     * @api
     *
     * @param array<\Generated\Shared\Transfer\EventEntityTransfer> $eventEntityTransfers
     * @param string $eventName
     */
    public function handleBulk(array $eventEntityTransfers, $eventName): void
    {
        $cmsVersionIds = $this->getFactory()->getEventBehaviorFacade()->getEventTransferIds($eventEntityTransfers);

        if (!$cmsVersionIds) {
            return;
        }

        $this->processCmsVersions($eventEntityTransfers);
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

        return $this->getConfig()->getCmsPageVersionPublishSubscribedEvents();
    }

    /**
     * @param array<\Generated\Shared\Transfer\EventEntityTransfer> $eventEntityTransfers
     */
    protected function processCmsVersions(array $eventEntityTransfers): void
    {
        // Extract CMS page IDs from event transfers using foreign keys
        $cmsPageIds = $this->getFactory()->getEventBehaviorFacade()->getEventTransferForeignKeys(
            $eventEntityTransfers,
            'spy_cms_version.fk_cms_page',
        );

        if (!$cmsPageIds) {
            return;
        }

        $cmsFacade = $this->getFactory()->getCmsFacade();
        $uniqueCmsPageIds = array_unique($cmsPageIds);

        foreach ($uniqueCmsPageIds as $idCmsPage) {
            $cmsPageTransfer = $cmsFacade->findCmsPageById($idCmsPage);
            if ($cmsPageTransfer === null) {
                continue;
            }

            $cmsVersionTransfer = $cmsFacade->findLatestCmsVersionByIdCmsPage($idCmsPage);
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
