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
     * Specification:
     * - Returns whether Algolia integration is active.
     * - Value is retrieved from shared configuration.
     *
     * @api
     */
    public function getIsActive(): bool
    {
        return $this->getSharedConfig()->getIsActive();
    }

    /**
     * Specification:
     * - Returns the tenant name for Algolia.
     * - Used to namespace indices when Application ID is used by multiple Spryker instances.
     * - Value is retrieved from shared configuration.
     *
     * @api
     */
    public function getTenantIdentifier(): string
    {
        return $this->getSharedConfig()->getTenantIdentifier();
    }

    /**
     * Specification:
     * - Returns the Algolia application ID.
     * - Used to identify the Algolia application in API requests.
     * - Value is retrieved from shared configuration.
     *
     * @api
     */
    public function getApplicationId(): string
    {
        return $this->getSharedConfig()->getApplicationId();
    }

    /**
     * Specification:
     * - Returns the search-only API key for Algolia.
     * - This key has read-only access and can be safely exposed to frontend.
     * - Used for search operations in client-side code.
     * - Value is retrieved from shared configuration.
     *
     * @api
     */
    public function getSearchOnlyApiKey(): string
    {
        return $this->getSharedConfig()->getSearchOnlyApiKey();
    }

    /**
     * Specification:
     * - Returns whether frontend search is enabled for products.
     * - When enabled, product searches are performed directly from frontend using Algolia.
     * - Value is retrieved from shared configuration.
     *
     * @api
     */
    public function isSearchInFrontendEnabledForProducts(): bool
    {
        return $this->getSharedConfig()->isSearchInFrontendEnabledForProducts();
    }

    /**
     * Specification:
     * - Returns whether frontend search is enabled for CMS pages.
     * - When enabled, CMS page searches are performed directly from frontend using Algolia.
     * - Value is retrieved from shared configuration.
     *
     * @api
     */
    public function isSearchInFrontendEnabledForCmsPages(): bool
    {
        return $this->getSharedConfig()->isSearchInFrontendEnabledForCmsPages();
    }

    /**
     * Specification:
     * - Returns the mappings between entities and Algolia indices.
     * - Used to determine which index to use for each entity type.
     * - Value is retrieved from shared configuration.
     *
     * @api
     */
    public function getEntityToIndexMappings(): array
    {
        return $this->getSharedConfig()->getEntityToIndexMappings();
    }
}
