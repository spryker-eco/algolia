<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Client\Algolia;

use Spryker\Client\Kernel\AbstractBundleConfig;
use SprykerEco\Shared\Algolia\AlgoliaConfig as SharedAlgoliaConfig;
use SprykerEco\Shared\Algolia\Enum\AlgoliaProductObjectEnum;

/**
 * @method \SprykerEco\Shared\Algolia\AlgoliaConfig getSharedConfig()
 */
class AlgoliaConfig extends AbstractBundleConfig
{
    /**
     * Used in Algolia API as UserAgent
     */
    public const string USER_AGENT_SEGMENT_NAME = 'Spryker Eco Algolia module';

    /**
     * Used in Algolia API as UserAgent
     */
    public const string VERSION = '1.0.0';

    public const string SOURCE_IDENTIFIER_PRODUCT = 'product';

    public const string SOURCE_IDENTIFIER_CMS_PAGE = 'cms-page';

    public const string FEATURE_PERSONALIZATION = 'personalization';

    /**
     * Specification:
     * - Returns whether Algolia integration is active.
     * - Active when application ID and search-only API key are non-empty.
     *
     * @api
     */
    public function getIsActive(): bool
    {
        return $this->getApplicationId() !== ''
            && $this->getSearchOnlyApiKey() !== '';
    }

    /**
     * Specification:
     * - Returns the tenant name for Algolia.
     * - Index prefix when Application ID is used by multiple Spryker instances.
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
     *
     * @api
     */
    public function getApplicationId(): string
    {
        if (!$this->getSharedConfig()->isConfigurationModuleUsed()) {
            return $this->getSharedConfig()->getApplicationId();
        }

        return (string)$this->getModuleConfig(SharedAlgoliaConfig::CONFIGURATION_KEY_APPLICATION_ID, '');
    }

    /**
     * Specification:
     * - Returns the search-only API key for Algolia.
     * - This key has read-only access and can be safely exposed to frontend.
     * - Used for search operations in client-side code.
     *
     * @api
     */
    public function getSearchOnlyApiKey(): string
    {
        if (!$this->getSharedConfig()->isConfigurationModuleUsed()) {
            return $this->getSharedConfig()->getSearchOnlyApiKey();
        }

        return (string)$this->getModuleConfig(SharedAlgoliaConfig::CONFIGURATION_KEY_SEARCH_ONLY_API_KEY, '');
    }

    /**
     * Specification:
     * - Returns whether product prices are synchronized to Algolia.
     * - When enabled, product prices are included in the indexed data.
     * - Value is retrieved from shared configuration.
     *
     * @api
     */
    public function getIsProductPriceSynced(): bool
    {
        return $this->getSharedConfig()->getIsProductPriceSynced();
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
        if (!$this->getSharedConfig()->isConfigurationModuleUsed()) {
            return $this->getSharedConfig()->isSearchInFrontendEnabledForProducts();
        }

        return $this->getModuleConfig(SharedAlgoliaConfig::CONFIGURATION_KEY_CATALOG_SEARCH_PROVIDER) === SharedAlgoliaConfig::SEARCH_PROVIDER_ALGOLIA;
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
        if (!$this->getSharedConfig()->isConfigurationModuleUsed()) {
            return $this->getSharedConfig()->isSearchInFrontendEnabledForCmsPages();
        }

        return $this->getModuleConfig(SharedAlgoliaConfig::CONFIGURATION_KEY_CMS_SEARCH_PROVIDER) === SharedAlgoliaConfig::SEARCH_PROVIDER_ALGOLIA;
    }

    public function isPersonalizationEnabled(): bool
    {
        return $this->getSharedConfig()->getIsPersonalizationEnabled();
    }

    /**
     * Specification:
     * - Returns the mappings between entities and Algolia indices.
     * - Used to determine which index to use for each entity type.
     * - Value is retrieved from shared configuration.
     *
     * @api
     *
     * @see \SprykerEco\Shared\Algolia\AlgoliaConfig::getEntityToIndexMappings() for expected structure.
     */
    public function getEntityToIndexMappings(): array
    {
        return $this->getSharedConfig()->getEntityToIndexMappings();
    }

    /**
     * Used by for correct events handing in "spryker-shop/traceable-event-widget"
     * Structure: [
     *    'sprykerFacetName' => 'algoliaFacetName',
     *    'sprykerFacetName' => '', // if mapping has to be skipped for the facet
     * ]
     *
     * @api
     *
     * @return array<string, string>
     */
    public function getProjectMappingFacets(): array
    {
        return [
            'price' => '',
            'color' => 'attributes.color',
            'brand' => 'attributes.brand',
        ];
    }

    /**
     * Specification:
     * - Returns the list of attributes that should be highlighted in search results.
     * - Highlighted attributes show matching search terms in bold or with special formatting.
     *
     * @api
     *
     * @return array<string>
     */
    public function getAttributesToHighlight(): array
    {
        return [
            AlgoliaProductObjectEnum::PRODUCT_ABSTRACT_SKU->value,
            AlgoliaProductObjectEnum::SKU->value,
            AlgoliaProductObjectEnum::NAME->value,
            AlgoliaProductObjectEnum::ABSTRACT_NAME->value,
            AlgoliaProductObjectEnum::CATEGORY->value,
        ];
    }

    /**
     * Specification:
     * - Returns the list of attributes that should not be displayed to users.
     * - These attributes are used internally for filtering but hidden from UI.
     *
     * @api
     *
     * @return array<string>
     */
    public function getNonDisplayAttributes(): array
    {
        return [AlgoliaProductObjectEnum::HIERARCHICAL_CATEGORIES->value];
    }

    /**
     * Specification:
     * - Returns the list of attributes that can be used for filtering.
     * - Includes afterDistinct wrapper for attributes to handle product variants.
     * - Conditionally includes price attribute based on configuration.
     * - Includes non-display attributes for internal filtering purposes.
     *
     * @api
     *
     * @return array<string>
     */
    public function getFilterableAttributes(): array
    {
        $attributes = [
            sprintf('searchable(%s)', AlgoliaProductObjectEnum::CATEGORY->value),
            AlgoliaProductObjectEnum::RATING->value,
            AlgoliaProductObjectEnum::LABEL->value,
            'attributes.color',
            'attributes.brand',
            AlgoliaProductObjectEnum::MERCHANT_NAME->value,
        ];
        if ($this->getIsProductPriceSynced()) {
            $attributes[] = AlgoliaProductObjectEnum::PRICES->value;
        }

        $result = [];
        foreach ($attributes as $attribute) {
            $result[] = sprintf('afterDistinct(%s)', $attribute);
        }

        return array_merge($result, $this->getNonDisplayAttributes());
    }
}
