<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Algolia;

use Spryker\Shared\ProductBundleStorage\ProductBundleStorageConfig;
use Spryker\Zed\Cms\Dependency\CmsEvents;
use Spryker\Zed\Kernel\AbstractBundleConfig;
use Spryker\Zed\PriceProduct\Dependency\PriceProductEvents;
use Spryker\Zed\Product\Dependency\ProductEvents;
use Spryker\Zed\ProductCategory\Dependency\ProductCategoryEvents;
use Spryker\Zed\ProductImage\Dependency\ProductImageEvents;
use Spryker\Zed\ProductLabel\Dependency\ProductLabelEvents;
use Spryker\Zed\ProductReview\Dependency\ProductReviewEvents;
use SprykerEco\Shared\Algolia\AlgoliaConfig as SharedAlgoliaConfig;
use SprykerEco\Shared\Algolia\Enum\AlgoliaCmsPageObjectEnum;
use SprykerEco\Shared\Algolia\Enum\AlgoliaProductObjectEnum;

/**
 * @method \SprykerEco\Shared\Algolia\AlgoliaConfig getSharedConfig()
 */
class AlgoliaConfig extends AbstractBundleConfig
{
    public const string FEATURE_PERSONALIZATION = 'personalization';

    /**
     * @var array<string>
     */
    public const array ALGOLIA_CREDENTIALS_KEYS = [
        SharedAlgoliaConfig::CONFIGURATION_KEY_APPLICATION_ID,
        SharedAlgoliaConfig::CONFIGURATION_KEY_SEARCH_ONLY_API_KEY,
        SharedAlgoliaConfig::CONFIGURATION_KEY_ADMIN_API_KEY,
    ];

    /**
     * Specification:
     * - Returns whether Algolia integration is active.
     * - Active when all three credentials are non-empty.
     *
     * @api
     */
    public function getIsActive(): bool
    {
        return $this->getApplicationId() !== ''
            && $this->getSearchOnlyApiKey() !== ''
            && $this->getAdminApiKey() !== '';
    }

    /**
     * Specification:
     * - Returns the tenant identifier for Algolia.
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

        return (string)$this->getModuleConfig(
            SharedAlgoliaConfig::CONFIGURATION_KEY_APPLICATION_ID,
            '',
        );
    }

    /**
     * Specification:
     * - Returns the admin API key for Algolia.
     * - This key has write access and should be kept secure on the backend.
     * - Used for indexing and administrative operations.
     *
     * @api
     */
    public function getAdminApiKey(): string
    {
        if (!$this->getSharedConfig()->isConfigurationModuleUsed()) {
            return $this->getSharedConfig()->getAdminApiKey();
        }

        return (string)$this->getModuleConfig(
            SharedAlgoliaConfig::CONFIGURATION_KEY_ADMIN_API_KEY,
            '',
        );
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

        return (string)$this->getModuleConfig(
            SharedAlgoliaConfig::CONFIGURATION_KEY_SEARCH_ONLY_API_KEY,
            '',
        );
    }

    /**
     * Specification:
     * - Returns whether frontend search is enabled for products.
     * - When enabled, product searches are performed directly from frontend using Algolia.
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
     * - Returns whether frontend search is enabled for CMS pages.
     * - When enabled, CMS page searches are performed directly from frontend using Algolia.
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

    /**
     * Specification:
     * - Returns the default chunk size for entity export operations.
     * - This value is used when no chunk size is specified in the console command.
     *
     * @api
     */
    public function getDefaultExportChunkSize(): int
    {
        return 100;
    }

    /**
     * Specification:
     * - Returns the list of events that trigger product concrete publishing to Algolia.
     * - Can be overridden in project-level config to add or remove events.
     * - Includes events from ProductBundleStorage, PriceProduct, and ProductSearch modules if available.
     *
     * @api
     *
     * @return array<string>
     */
    public function getProductConcreteSubscribedEvents(): array
    {
        $events = [
            ProductEvents::PRODUCT_CONCRETE_PUBLISH,
            ProductEvents::PRODUCT_CONCRETE_UPDATE,
            ProductEvents::ENTITY_SPY_PRODUCT_CREATE,
            ProductEvents::ENTITY_SPY_PRODUCT_UPDATE,
            ProductEvents::ENTITY_SPY_PRODUCT_LOCALIZED_ATTRIBUTES_CREATE,
            ProductEvents::ENTITY_SPY_PRODUCT_LOCALIZED_ATTRIBUTES_UPDATE,
            ProductImageEvents::PRODUCT_IMAGE_PRODUCT_CONCRETE_PUBLISH,
            ProductImageEvents::ENTITY_SPY_PRODUCT_IMAGE_SET_CREATE,
            ProductImageEvents::ENTITY_SPY_PRODUCT_IMAGE_SET_UPDATE,
            ProductImageEvents::ENTITY_SPY_PRODUCT_IMAGE_SET_TO_PRODUCT_IMAGE_CREATE,
            ProductImageEvents::ENTITY_SPY_PRODUCT_IMAGE_SET_TO_PRODUCT_IMAGE_UPDATE,
        ];

        // Add ProductBundleStorage events if module exists
        if (class_exists('Spryker\Shared\ProductBundleStorage\ProductBundleStorageConfig')) {
            $events[] = ProductBundleStorageConfig::PRODUCT_BUNDLE_PUBLISH;
            $events[] = ProductBundleStorageConfig::ENTITY_SPY_PRODUCT_BUNDLE_CREATE;
            $events[] = ProductBundleStorageConfig::ENTITY_SPY_PRODUCT_BUNDLE_UPDATE;
        }

        // Add PriceProduct events if module exists
        if (class_exists('Spryker\Zed\PriceProduct\Dependency\PriceProductEvents')) {
            $events[] = PriceProductEvents::PRICE_CONCRETE_PUBLISH;
            $events[] = PriceProductEvents::ENTITY_SPY_PRICE_PRODUCT_CREATE;
            $events[] = PriceProductEvents::ENTITY_SPY_PRICE_PRODUCT_UPDATE;
        }

        return $events;
    }

    /**
     * Specification:
     * - Returns the list of events that trigger product abstract publishing to Algolia.
     * - Can be overridden in project-level config to add or remove events.
     * - Includes events from PriceProduct, ProductCategory, ProductLabel, ProductReview, and ProductImage modules if available.
     *
     * @api
     *
     * @return array<string>
     */
    public function getProductAbstractSubscribedEvents(): array
    {
        $events = [
            ProductEvents::PRODUCT_ABSTRACT_PUBLISH,
            ProductEvents::PRODUCT_ABSTRACT_UPDATE,
            ProductEvents::ENTITY_SPY_PRODUCT_ABSTRACT_UPDATE,
            ProductEvents::ENTITY_SPY_URL_CREATE,
            ProductEvents::ENTITY_SPY_URL_UPDATE,
            ProductEvents::ENTITY_SPY_PRODUCT_ABSTRACT_STORE_CREATE,
            ProductEvents::ENTITY_SPY_PRODUCT_ABSTRACT_STORE_UPDATE,
            ProductEvents::ENTITY_SPY_PRODUCT_ABSTRACT_LOCALIZED_ATTRIBUTES_CREATE,
            ProductEvents::ENTITY_SPY_PRODUCT_ABSTRACT_LOCALIZED_ATTRIBUTES_UPDATE,
            ProductCategoryEvents::PRODUCT_CATEGORY_PUBLISH,
            ProductCategoryEvents::ENTITY_SPY_PRODUCT_CATEGORY_CREATE,
            ProductCategoryEvents::ENTITY_SPY_PRODUCT_CATEGORY_DELETE,
            ProductImageEvents::PRODUCT_IMAGE_PRODUCT_ABSTRACT_PUBLISH,
            ProductImageEvents::ENTITY_SPY_PRODUCT_IMAGE_SET_CREATE,
            ProductImageEvents::ENTITY_SPY_PRODUCT_IMAGE_SET_UPDATE,
        ];

        // Add PriceProduct events if module exists
        if (class_exists('Spryker\Zed\PriceProduct\Dependency\PriceProductEvents')) {
            $events[] = PriceProductEvents::PRICE_ABSTRACT_PUBLISH;
            $events[] = PriceProductEvents::ENTITY_SPY_PRICE_PRODUCT_CREATE;
            $events[] = PriceProductEvents::ENTITY_SPY_PRICE_PRODUCT_UPDATE;
        }

        // Add ProductLabel events if module exists
        if (class_exists('Spryker\Zed\ProductLabel\Dependency\ProductLabelEvents')) {
            $events[] = ProductLabelEvents::ENTITY_SPY_PRODUCT_LABEL_PRODUCT_ABSTRACT_CREATE;
            $events[] = ProductLabelEvents::ENTITY_SPY_PRODUCT_LABEL_PRODUCT_ABSTRACT_DELETE;
        }

        // Add ProductReview events if module exists
        if (class_exists('Spryker\Zed\ProductReview\Dependency\ProductReviewEvents')) {
            $events[] = ProductReviewEvents::PRODUCT_ABSTRACT_REVIEW_PUBLISH;
            $events[] = ProductReviewEvents::ENTITY_SPY_PRODUCT_REVIEW_CREATE;
            $events[] = ProductReviewEvents::ENTITY_SPY_PRODUCT_REVIEW_UPDATE;
        }

        return $events;
    }

    /**
     * Specification:
     * - Returns the list of events that trigger product unpublishing from Algolia.
     * - Can be overridden in project-level config to add or remove events.
     *
     * @api
     *
     * @return array<string>
     */
    public function getProductConcreteUnpublishSubscribedEvents(): array
    {
        return [
            ProductEvents::PRODUCT_CONCRETE_UNPUBLISH,
            ProductEvents::ENTITY_SPY_PRODUCT_DELETE,
        ];
    }

    /**
     * Specification:
     * - Returns the list of attributes that are searchable in Algolia.
     * - These attributes are indexed and can be searched by users.
     * - Order determines the priority of attributes in search ranking.
     *
     * @api
     *
     * @return array<string>
     */
    public function getSearchableAttributes(): array
    {
        return [
            AlgoliaProductObjectEnum::SKU->value,
            AlgoliaProductObjectEnum::PRODUCT_ABSTRACT_SKU->value,
            AlgoliaProductObjectEnum::NAME->value,
            AlgoliaProductObjectEnum::ABSTRACT_NAME->value,
            AlgoliaProductObjectEnum::CATEGORY->value,
            AlgoliaProductObjectEnum::KEYWORDS->value,
            'attributes.brand',
            AlgoliaProductObjectEnum::DESCRIPTION->value,
        ];
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
     * - Returns the searchable attributes for CMS pages.
     * - These attributes are indexed and can be searched by users.
     * - Includes content, metadata, and SEO-related fields.
     *
     * @api
     *
     * @return array<string>
     */
    public function getCmsPageSearchableAttributes(): array
    {
        return [
            AlgoliaCmsPageObjectEnum::NAME->value,
            AlgoliaCmsPageObjectEnum::TITLE->value,
            AlgoliaCmsPageObjectEnum::CONTENT->value,
            AlgoliaCmsPageObjectEnum::META_TITLE->value,
            AlgoliaCmsPageObjectEnum::META_DESCRIPTION->value,
            AlgoliaCmsPageObjectEnum::META_KEYWORDS->value,
        ];
    }

    /**
     * Specification:
     * - Returns the custom ranking attributes for CMS pages.
     * - Used to determine the order of CMS pages in search results.
     * - Currently ranks by last updated date in descending order.
     *
     * @api
     *
     * @return array<string>
     */
    public function getCmsPageCustomRanking(): array
    {
        return [
            'desc(' . AlgoliaCmsPageObjectEnum::LAST_UPDATED->value . ')',
        ];
    }

    /**
     * Specification:
     * - Returns the list of facet attributes used for Query Suggestions generation.
     * - Each entry is an array of attribute names forming one facet group.
     * - Empty by default; override in project-level config to enable suggestion facets.
     *
     * @api
     *
     * @return array<array<string>>
     */
    public function getSuggestionGenerateAttributes(): array
    {
        return [];
    }

    /**
     * Specification:
     * - Returns the list of sortable attribute names for product indices.
     * - Each attribute gets asc/desc replica indices created for it.
     * - The attribute name is used for both the replica index naming and the ranking.
     * - Empty by default; override in project-level config to enable sorting.
     *
     * @api
     *
     * @return array<string>
     */
    public function getProductSortingAttributes(): array
    {
        return [];
    }

    /**
     * Specification:
     * - Returns the sorting attributes available for CMS pages.
     * - These attributes can be used to create replica indices for different sort orders.
     * - Empty by default; override in project-level config to enable sorting.
     *
     * @api
     *
     * @return array<string>
     */
    public function getCmsPageSortingAttributes(): array
    {
        return [];
    }

    /**
     * Specification:
     * - Returns the list of events that trigger CMS page update/publish to Algolia.
     * - Can be overridden in project-level config to add or remove events.
     *
     * @api
     *
     * @return array<string>
     */
    public function getCmsPageUpdateSubscribedEvents(): array
    {
        return [
            CmsEvents::ENTITY_SPY_CMS_PAGE_UPDATE,
        ];
    }

    /**
     * Specification:
     * - Returns the list of events that trigger CMS page version publish to Algolia.
     * - Can be overridden in project-level config to add or remove events.
     *
     * @api
     *
     * @return array<string>
     */
    public function getCmsPageVersionPublishSubscribedEvents(): array
    {
        return [
            CmsEvents::CMS_VERSION_PUBLISH,
            CmsEvents::ENTITY_SPY_CMS_VERSION_CREATE,
        ];
    }
}
