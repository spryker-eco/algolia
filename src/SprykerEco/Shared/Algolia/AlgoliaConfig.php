<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Shared\Algolia;

use Spryker\Shared\Kernel\AbstractBundleConfig;

class AlgoliaConfig extends AbstractBundleConfig
{
    /**
     * @api
     *
     * @return string
     */
    public function getTenantIdentifier(): string
    {
        return $this->get(AlgoliaConstants::TENANT_IDENTIFIER, 'production');
    }

    /**
     * @api
     *
     * @return string
     */
    public function getApplicationId(): string
    {
        return $this->get(AlgoliaConstants::APPLICATION_ID, '');
    }

    /**
     * @api
     *
     * @return string
     */
    public function getAdminApiKey(): string
    {
        return $this->get(AlgoliaConstants::ADMIN_API_KEY, '');
    }

    /**
     * @api
     *
     * @return string
     */
    public function getSearchOnlyApiKey(): string
    {
        return $this->get(AlgoliaConstants::SEARCH_ONLY_API_KEY, '');
    }

    /**
     * Specification:
     *  - Defines whether Algolia search is enabled for products in the frontend.
     *
     * @api
     */
    public function isSearchInFrontendEnabledForProducts(): bool
    {
        return true;
    }

    /**
     * Specification:
     * - Defines whether product prices are indexed (if your products do not have prices should be `false`).
     *
     * @api
     */
    public function getIsProductPriceSynced(): bool
    {
        return true;
    }

    /**
     *  Specification:
     *  - Defines whether Algolia search is enabled for CMS pages in the frontend.
     *
     * @api
     */
    public function isSearchInFrontendEnabledForCmsPages(): bool
    {
        return true;
    }

    /**
     *  Specification:
     *  - Defines Spryker custom entities to Algolia index mapping.
     *
     * @example TODO: see EntityToIndexMappingTransfer for structure of arrays
     *
     * @api
     */
    public function getEntityToIndexMappings(): array
    {
        return [];
    }
}
