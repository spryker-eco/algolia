<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Shared\Algolia;

use Spryker\Shared\Kernel\AbstractSharedConfig;

class AlgoliaConfig extends AbstractSharedConfig
{
    public const string CONFIGURATION_KEY_APPLICATION_ID = 'integrations:algolia:credentials:application_id';

    public const string CONFIGURATION_KEY_SEARCH_ONLY_API_KEY = 'integrations:algolia:credentials:search_only_api_key';

    public const string CONFIGURATION_KEY_ADMIN_API_KEY = 'integrations:algolia:credentials:admin_api_key';

    public const string CONFIGURATION_KEY_CATALOG_SEARCH_PROVIDER = 'catalog:catalog_search:provider:search_provider';

    public const string CONFIGURATION_KEY_CMS_SEARCH_PROVIDER = 'cms:cms_search:provider:search_provider';

    public const string SEARCH_PROVIDER_ALGOLIA = 'algolia';

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
     * - Defines the delimiter each multi-value product attribute is split by, keyed by attribute name.
     * - The listed attributes hold several values in one delimiter separated string and are indexed as an array,
     *   so the index produces one facet bucket per value instead of one per distinct combination.
     * - Keys are plain attribute keys as they appear under the `attributes` key of the Algolia product record,
     *   for example `color`, not `attributes.color`.
     * - The delimiter is defined per attribute because the same character can belong to the value of another
     *   attribute, for example the comma in the decimal number `5,5` or in a free text attribute.
     * - Splitting is opt-in: an attribute that is not listed here is indexed verbatim.
     * - Changing this configuration requires a full product export before it takes effect on the storefront.
     * - Empty by default; override on project level.
     *
     * @examples
     * [
     *     'color' => ',', // "red,blue" is indexed as ["red", "blue"]
     *     'material' => ',', // "cotton, wool" is indexed as ["cotton", "wool"], every value is trimmed
     *     'size' => '|', // "5,5Gb|6,5Gb" is indexed as ["5,5Gb", "6,5Gb"], the commas stay inside the values
     * ]
     *
     * @api
     *
     * @return array<string, string>
     */
    public function getMultiValueProductAttributeDelimiters(): array
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

    /**
     * Specification:
     * - Returns whether the Configuration module is used for Algolia configuration.
     * - When enabled, configuration values are retrieved from the Configuration module.
     * - When disabled, configuration values are retrieved from static Shared config.
     *
     * @api
     */
    public function isConfigurationModuleUsed(): bool
    {
        return false;
    }
}
