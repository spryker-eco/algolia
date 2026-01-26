<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Communication\Plugin\Publisher\Product;

use Generated\Shared\Transfer\ProductDeletedTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\PublisherExtension\Dependency\Plugin\PublisherPluginInterface;

/**
 * Unpublishes deleted products from Algolia.
 *
 * @method \SprykerEco\Zed\Algolia\Business\AlgoliaFacadeInterface getFacade()
 * @method \SprykerEco\Zed\Algolia\AlgoliaConfig getConfig()
 * @method \SprykerEco\Zed\Algolia\Communication\AlgoliaCommunicationFactory getFactory()
 */
class AlgoliaProductConcreteDeletePublisherPlugin extends AbstractPlugin implements PublisherPluginInterface
{
    /**
     * {@inheritDoc}
     * - Unpublishes product concrete data from Algolia.
     * - Extracts product SKUs from event entity transfers.
     * - Removes products from Algolia indices.
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
        foreach ($eventEntityTransfers as $eventEntityTransfer) {
            $sku = $this->extractSku($eventEntityTransfer);

            if ($sku === null) {
                continue;
            }

            $productDeletedTransfer = (new ProductDeletedTransfer())
                ->setSku($sku);

            $this->getFacade()->deleteProduct($productDeletedTransfer);
        }
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

        return $this->getConfig()->getProductConcreteUnpublishSubscribedEvents();
    }

    /**
     * @param \Generated\Shared\Transfer\EventEntityTransfer $eventEntityTransfer
     *
     * @return string|null
     */
    protected function extractSku($eventEntityTransfer): ?string
    {
        $originalValues = $eventEntityTransfer->getOriginalValues();

        if (isset($originalValues['sku'])) {
            return $originalValues['sku'];
        }

        $modifiedColumns = $eventEntityTransfer->getModifiedColumns();

        if (isset($modifiedColumns['sku'])) {
            return $modifiedColumns['sku'];
        }

        return null;
    }
}
