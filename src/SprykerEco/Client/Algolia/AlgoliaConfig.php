<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Algolia;

use Spryker\Client\Kernel\AbstractBundleConfig;

/**
 * @method \SprykerEco\Shared\Algolia\AlgoliaConfig getSharedConfig()
 */
class AlgoliaConfig extends AbstractBundleConfig
{
    /**
     * @api
     *
     * @return string
     */
    public function getTenantIdentifier(): string
    {
        return $this->getSharedConfig()->getTenantIdentifier();
    }

    /**
     * @api
     *
     * @return string
     */
    public function getApplicationId(): string
    {
        return $this->getSharedConfig()->getApplicationId();
    }

    /**
     * @api
     *
     * @return string
     */
    public function getSearchOnlyApiKey(): string
    {
        return $this->getSharedConfig()->getSearchOnlyApiKey();
    }

    /**
     * @api
     *
     * @return bool
     */
    public function isSearchInFrontendEnabledForProducts(): bool
    {
        return $this->getSharedConfig()->isSearchInFrontendEnabledForProducts();
    }

    /**
     * @api
     *
     * @return bool
     */
    public function isSearchInFrontendEnabledForCmsPages(): bool
    {
        return $this->getSharedConfig()->isSearchInFrontendEnabledForCmsPages();
    }

    /**
     * @api
     */
    public function getEntityToIndexMappings(): array
    {
        return $this->getSharedConfig()->getEntityToIndexMappings();
    }
}
