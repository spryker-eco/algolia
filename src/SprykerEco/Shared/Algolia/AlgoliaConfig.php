<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Shared\Algolia;

use Spryker\Shared\Kernel\AbstractSharedConfig;

class AlgoliaConfig extends AbstractSharedConfig
{
 /**
  * Specification:
  * - Returns whether Algolia integration is active.
  *
  * @api
  */
    public function getIsActive(): bool
    {
        return $this->get(AlgoliaConstants::IS_ACTIVE, false);
    }

    /**
     * @api
     */
    public function getTenantIdentifier(): string
    {
        return $this->get(AlgoliaConstants::TENANT_IDENTIFIER, 'production');
    }

    /**
     * @api
     */
    public function getApplicationId(): string
    {
        return $this->get(AlgoliaConstants::APPLICATION_ID, '');
    }

    /**
     * @api
     */
    public function getAdminApiKey(): string
    {
        return $this->get(AlgoliaConstants::ADMIN_API_KEY, '');
    }

    /**
     * @api
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
        return false;
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
        return false;
    }

    /**
     *  Specification:
     *  - Defines Spryker custom entities to Algolia index mapping.
     *  - See details https://docs.spryker.com/docs/pbc/all/search/latest/base-shop/third-party-integrations/algolia/algolia-search-by-custom-entity-index#step-by-step-instructions
     *
     * @examples
     * [
     *   [
     *      'sourceIdentifier' => 'document', // is provided in your plugin AlgoliaSearchQueryPlugin in `SearchContextTransfer.sourceIdentifier`
     *      'store' => 'DE',
     *      'locales' => ['de_DE'],
     *      'indexName' => 'documents_DE', // existing index name in Algolia account
     *   ],
     *   [
     *      'sourceIdentifier' => 'manufacturer',
     *      'store' => '*', // applicable to all stores
     *      'locales' => ['*'], // applicable to any locale
     *      'indexName' => 'manufacturers', // existing index name in Algolia account
     *   ],
     * ]
     *
     * @api
     */
    public function getEntityToIndexMappings(): array
    {
        return [];
    }

    /**
     * Specification:
     * - Defines whether personalization is enabled. Requires Algolia premium subscription.
     *
     * @api
     */
    public function getIsPersonalizationEnabled(): bool
    {
        return true;
    }
}
