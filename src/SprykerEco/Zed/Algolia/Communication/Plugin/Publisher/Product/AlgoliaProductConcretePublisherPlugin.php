<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Communication\Plugin\Publisher\Product;

/**
 * Publishes product concrete data to Algolia on create and update events.
 *
 * @method \SprykerEco\Zed\Algolia\Business\AlgoliaFacadeInterface getFacade()
 * @method \SprykerEco\Zed\Algolia\AlgoliaConfig getConfig()
 * @method \SprykerEco\Zed\Algolia\Communication\AlgoliaCommunicationFactory getFactory()
 */
class AlgoliaProductConcretePublisherPlugin extends AbstractAlgoliaProductPublisherPlugin
{
    /**
     * {@inheritDoc}
     * - Publishes product concrete data to Algolia.
     * - Extracts product IDs from event entity transfers.
     * - Fetches product concrete data with abstract data.
     * - Sends product data to Algolia for indexing.
     *
     * @api
     *
     * @param array<\Generated\Shared\Transfer\EventEntityTransfer> $eventEntityTransfers
     * @param string $eventName
     *
     * @return void
     */
    public function handleBulk(array $eventEntityTransfers, $eventName)
    {
        $productConcreteTransfers = $this->getProductConcreteTransfersByEventEntityTransfers($eventEntityTransfers);

        if ($productConcreteTransfers->count() === 0) {
            return;
        }

        $this->getFacade()->updateProducts($productConcreteTransfers);
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

        return $this->getConfig()->getProductConcreteSubscribedEvents();
    }
}
