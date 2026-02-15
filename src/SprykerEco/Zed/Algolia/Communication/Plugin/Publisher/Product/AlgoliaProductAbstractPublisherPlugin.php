<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Communication\Plugin\Publisher\Product;

/**
 * Publishes product abstract-related changes to Algolia (publishes all concrete products of the abstract).
 *
 * @method \SprykerEco\Zed\Algolia\Business\AlgoliaFacadeInterface getFacade()
 * @method \SprykerEco\Zed\Algolia\AlgoliaConfig getConfig()
 * @method \SprykerEco\Zed\Algolia\Communication\AlgoliaCommunicationFactory getFactory()
 */
class AlgoliaProductAbstractPublisherPlugin extends AbstractAlgoliaProductPublisherPlugin
{
    /**
     * {@inheritDoc}
     * - Publishes product abstract-related data to Algolia.
     * - Extracts product abstract IDs from event entity transfers.
     * - Fetches all active concrete products for those abstracts.
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
        $productAbstractIds = $this->extractProductAbstractIds($eventEntityTransfers);

        if (!$productAbstractIds) {
            return;
        }

        $productConcreteTransfers = $this->getProductConcretesByAbstractIds($productAbstractIds);

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

        return $this->getConfig()->getProductAbstractSubscribedEvents();
    }
}
