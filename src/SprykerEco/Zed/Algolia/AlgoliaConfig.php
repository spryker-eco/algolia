<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Algolia;

use Generated\Shared\Transfer\AlgoliaConfigTransfer;
use Generated\Shared\Transfer\FacetCollectionTransfer;
use Spryker\Shared\ProductBundleStorage\ProductBundleStorageConfig;
use Spryker\Zed\Kernel\AbstractBundleConfig;
use Spryker\Zed\PriceProduct\Dependency\PriceProductEvents;
use Spryker\Zed\Product\Dependency\ProductEvents;
use Spryker\Zed\ProductCategory\Dependency\ProductCategoryEvents;
use Spryker\Zed\ProductImage\Dependency\ProductImageEvents;
use Spryker\Zed\ProductLabel\Dependency\ProductLabelEvents;
use Spryker\Zed\ProductReview\Dependency\ProductReviewEvents;
use Spryker\Zed\ProductSearch\Dependency\ProductSearchEvents;
use SprykerEco\Shared\Algolia\Enum\AlgoliaCmsPageObjectEnum;

/**
 * @method \SprykerEco\Shared\Algolia\AlgoliaConfig getSharedConfig()
 */
class AlgoliaConfig extends AbstractBundleConfig
{
    /**
     * Used in Algolia API as UserAgent
     *
     * @var string
     */
    public const APP_VERSION = '3.0.0';

    /**
     * Used in Algolia API as UserAgent
     *
     * @var string
     */
    public const USER_AGENT_SEGMENT_NAME = 'spryker-integration';

    /**
     * @var string
     */
    public const FEATURE_PERSONALIZATION = 'personalization';

    /**
     * @var string
     */
    public const ALGOLIA_INDEX_REPLICA_NAME_TEMPLATE_SORT_DESC = '%s-desc-%s';

    /**
     * @var string
     */
    public const ALGOLIA_INDEX_REPLICA_NAME_TEMPLATE_SORT_ASC = '%s-asc-%s';

    /**
     * @var string
     */
    public const ATTRIBUTE_NAME_DESCRIPTION = 'description';

    /**
     * @var string
     */
    public const ATTRIBUTE_NAME_KEYWORDS = 'keywords';

    /**
     * @var string
     */
    public const ATTRIBUTE_NAME_LABEL = 'label';

    /**
     * @var string
     */
    public const ATTRIBUTE_NAME_BRAND = 'attributes.brand';

    /**
     * @var string
     */
    public const ATTRIBUTE_NAME_COLOR = 'attributes.color';

    /**
     * @var string
     */
    public const ATTRIBUTE_NAME_CATEGORY = 'category';

    /**
     * @var string
     */
    public const ATTRIBUTE_NAME_SKU = 'sku';

    /**
     * @var string
     */
    public const ATTRIBUTE_NAME_MERCHANT_NAME = 'merchant_name';

    /**
     * @var string
     */
    public const ATTRIBUTE_NAME_PRICES = 'prices';

    /**
     * @var string
     */
    public const ATTRIBUTE_NAME_RATING = 'rating';

    /**
     * @var string
     */
    public const ATTRIBUTE_NAME_PRODUCT_ABSTRACT_SKU = 'product_abstract_sku';

    /**
     * @var string
     */
    public const ATTRIBUTE_NAME_NAME = 'name';

    /**
     * @var string
     */
    public const ATTRIBUTE_NAME_ABSTRACT_NAME = 'abstract_name';

    /**
     * @var string
     */
    public const INDEXED_PRODUCT_FIELD_NAME_PRODUCT_ABSTRACT_SKU = self::ATTRIBUTE_NAME_PRODUCT_ABSTRACT_SKU;

    /**
     * @var string
     */
    public const INDEXED_PRODUCT_FIELD_NAME_NAME = self::ATTRIBUTE_NAME_NAME;

    /**
     * @var string
     */
    public const INDEXED_PRODUCT_FIELD_NAME_SKU = self::ATTRIBUTE_NAME_SKU;

    /**
     * @var string
     */
    public const INDEXED_PRODUCT_FIELD_NAME_DESCRIPTION = self::ATTRIBUTE_NAME_DESCRIPTION;

    /**
     * @var string
     */
    public const INDEXED_PRODUCT_FIELD_NAME_KEYWORDS = self::ATTRIBUTE_NAME_KEYWORDS;

    /**
     * @var string
     */
    public const INDEXED_PRODUCT_FIELD_NAME_ABSTRACT_NAME = self::ATTRIBUTE_NAME_ABSTRACT_NAME;

    /**
     * @var string
     */
    public const INDEXED_PRODUCT_FIELD_NAME_MERCHANT_NAME = self::ATTRIBUTE_NAME_MERCHANT_NAME;

    /**
     * @var string
     */
    public const INDEXED_PRODUCT_FIELD_NAME_CATEGORY = self::ATTRIBUTE_NAME_CATEGORY;

    /**
     * @var string
     */
    public const INDEXED_PRODUCT_FIELD_NAME_LABEL = self::ATTRIBUTE_NAME_LABEL;

    /**
     * @var string
     */
    public const INDEXED_PRODUCT_FIELD_NAME_PRICES = self::ATTRIBUTE_NAME_PRICES;

    /**
     * @var string
     */
    public const INDEXED_PRODUCT_FIELD_NAME_RATING = self::ATTRIBUTE_NAME_RATING;

    /**
     * @var string
     */
    public const INDEXED_PRODUCT_FIELD_NAME_MERCHANT_REFERENCE = 'merchant_reference';

    /**
     * @var string
     */
    public const INDEXED_PRODUCT_FIELD_NAME_ATTRIBUTES = 'attributes';

    /**
     * @var string
     */
    public const INDEXED_PRODUCT_FIELD_NAME_HIERARCHICAL_CATEGORIES = 'hierarchical_categories';

    /**
     * @var string
     */
    public const INDEXED_PRODUCT_FIELD_NAME_IMAGES = 'images';

    /**
     * @var string
     */
    public const INDEXED_PRODUCT_FIELD_NAME_URL = 'url';

    /**
     * @var string
     */
    public const INDEXED_PRODUCT_FIELD_NAME_SEARCH_METADATA = 'search_metadata';

    /**
     * @var string
     */
    public const INDEXED_PRODUCT_FIELD_NAME_CONCRETE_PRICES = 'concrete_prices';

    /**
     * @var string
     */
    public const FILTER_NAME_PRICE = 'price';

    /**
     * @var string
     */
    public const FILTER_NAME_CURRENCY = 'currency';

    /**
     * @var string
     */
    public const FILTER_NAME_PRICING_MODE = 'price_mode';

    /**
     * @var array<string, mixed>
     */
    public const CMS_PAGE_PARAMETERS = [
        'attributesToSnippet' => ['content:50'],
        'snippetEllipsisText' => '...',
        'attributesToHighlight' => ['name'],
    ];

    /**
     * Specification:
     * - Returns the suffix used for query suggestions index names.
     * - Used to distinguish query suggestion indices from main product indices.
     *
     * @api
     */
    public function getQuerySuggestionsSuffix(): string
    {
        return 'query_suggestions';
    }

    /**
     * @var array<string>
     */
    public const NON_ATTRIBUTE_FIELDS = [
        self::INDEXED_PRODUCT_FIELD_NAME_PRODUCT_ABSTRACT_SKU,
        self::INDEXED_PRODUCT_FIELD_NAME_SKU,
        self::INDEXED_PRODUCT_FIELD_NAME_NAME,
        self::INDEXED_PRODUCT_FIELD_NAME_DESCRIPTION,
        self::INDEXED_PRODUCT_FIELD_NAME_KEYWORDS,
        self::INDEXED_PRODUCT_FIELD_NAME_ABSTRACT_NAME,
        self::INDEXED_PRODUCT_FIELD_NAME_MERCHANT_NAME,
        self::INDEXED_PRODUCT_FIELD_NAME_MERCHANT_REFERENCE,
        self::INDEXED_PRODUCT_FIELD_NAME_CATEGORY,
        self::INDEXED_PRODUCT_FIELD_NAME_HIERARCHICAL_CATEGORIES,
        self::INDEXED_PRODUCT_FIELD_NAME_IMAGES,
        self::INDEXED_PRODUCT_FIELD_NAME_LABEL,
        self::INDEXED_PRODUCT_FIELD_NAME_PRICES,
        self::INDEXED_PRODUCT_FIELD_NAME_RATING,
        self::INDEXED_PRODUCT_FIELD_NAME_URL,
        self::INDEXED_PRODUCT_FIELD_NAME_CONCRETE_PRICES,
    ];

    /**
     * @var array<string>
     */
    public const RESTRICTED_TO_USE_AS_FACET_FIELD_NAMES = [
        self::INDEXED_PRODUCT_FIELD_NAME_PRICES,
        self::INDEXED_PRODUCT_FIELD_NAME_CONCRETE_PRICES,
    ];

    /**
     * @var string
     */
    protected const ATTRIBUTE_PREFIX = self::INDEXED_PRODUCT_FIELD_NAME_ATTRIBUTES . '.';

    /**
     * @var string
     */
    protected const SEARCH_METADATA_PREFIX_UNDERSCORE = self::INDEXED_PRODUCT_FIELD_NAME_SEARCH_METADATA . '_';

    /**
     * @var string
     */
    protected const SEARCH_METADATA_PREFIX_DOT = self::INDEXED_PRODUCT_FIELD_NAME_SEARCH_METADATA . '.';

    /**
     * @var string
     */
    protected const PRICE_FACET_KEY_TEMPLATE = 'prices.%s.%s';

    /**
     * @var string
     */
    protected const AFTER_DISTINCT_PARAM_TEMPLATE = 'afterDistinct(%s)';

    /**
     * @var string
     */
    protected const SEARCHABLE_PARAM_TEMPLATE = 'searchable(%s)';

    /**
     * @var array<string, string>
     */
    protected const PRICE_MODE_MAPPING = [
        'GROSS_MODE' => 'gross',
        'NET_MODE' => 'net',
    ];

    /**
     * @var array<string>
     */
    protected const ATTRIBUTES_TO_HIGHLIGHT_FIELDS = [
        self::INDEXED_PRODUCT_FIELD_NAME_PRODUCT_ABSTRACT_SKU,
        self::INDEXED_PRODUCT_FIELD_NAME_SKU,
        self::INDEXED_PRODUCT_FIELD_NAME_NAME,
        self::INDEXED_PRODUCT_FIELD_NAME_ABSTRACT_NAME,
        self::INDEXED_PRODUCT_FIELD_NAME_CATEGORY,
    ];

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
     * - Returns the tenant identifier for Algolia.
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
     * - Returns the admin API key for Algolia.
     * - This key has write access and should be kept secure on the backend.
     * - Used for indexing and administrative operations.
     * - Value is retrieved from shared configuration.
     *
     * @api
     */
    public function getAdminApiKey(): string
    {
        return $this->getSharedConfig()->getAdminApiKey();
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

        // Add ProductSearch events if module exists
        if (class_exists('Spryker\Zed\ProductSearch\Dependency\ProductSearchEvents')) {
            $events[] = ProductSearchEvents::ENTITY_SPY_PRODUCT_SEARCH_CREATE;
            $events[] = ProductSearchEvents::ENTITY_SPY_PRODUCT_SEARCH_UPDATE;
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
     * - Transforms a field key into the corresponding Algolia facet field key.
     * - Handles special cases like price facets with currency and pricing mode.
     * - Adds 'attributes.' prefix for attribute fields.
     * - Transforms search_metadata fields to use dot notation.
     * - Returns the field key as-is for non-attribute fields.
     *
     * @api
     */
    public function getAlgoliaFacetFieldKey(string $fieldKey, FacetCollectionTransfer $facetCollectionTransfer): string
    {
        if ($fieldKey === static::FILTER_NAME_PRICE) {
            return $this->getPriceFacetKey($facetCollectionTransfer);
        }

        if (in_array($fieldKey, static::NON_ATTRIBUTE_FIELDS, true)) {
            return $fieldKey;
        }

        if (str_starts_with($fieldKey, static::SEARCH_METADATA_PREFIX_DOT)) {
            return $fieldKey;
        }

        if (str_starts_with($fieldKey, static::SEARCH_METADATA_PREFIX_UNDERSCORE)) {
            return $this->transformSearchMetadataFacetKey($fieldKey);
        }

        return static::ATTRIBUTE_PREFIX . $fieldKey;
    }

    protected function transformSearchMetadataFacetKey(string $fieldKey): string
    {
        // This is needed to support SCOS request format coming from different applications
        return static::SEARCH_METADATA_PREFIX_DOT . substr($fieldKey, strlen(static::SEARCH_METADATA_PREFIX_UNDERSCORE));
    }

    /**
     * Specification:
     * - Returns the price facet key based on currency and pricing mode.
     * - Formats the key as 'prices.{currency}.{price_mode}' when currency and pricing mode are available.
     * - Falls back to 'price' if currency or pricing mode facets are not present.
     *
     * @api
     */
    public function getPriceFacetKey(FacetCollectionTransfer $facetCollectionTransfer): string
    {
        $facetsTransfers = $facetCollectionTransfer->getFacets();

        if (
            $facetsTransfers->offsetExists(static::FILTER_NAME_CURRENCY)
            && $facetsTransfers->offsetExists(static::FILTER_NAME_PRICING_MODE)
        ) {
            $currency = $facetsTransfers->offsetGet(static::FILTER_NAME_CURRENCY)->getParameters()->getValues()[0];
            $pricingMode = $facetsTransfers->offsetGet(static::FILTER_NAME_PRICING_MODE)->getParameters()->getValues()[0];

            return sprintf(static::PRICE_FACET_KEY_TEMPLATE, strtolower($currency), static::PRICE_MODE_MAPPING[$pricingMode] ?? 'gross');
        }

        return static::FILTER_NAME_PRICE;
    }

    /**
     * Specification:
     * - Returns the list of field names that are restricted from being used as facets.
     * - These fields cannot be used for filtering due to their data structure or purpose.
     *
     * @api
     *
     * @return array<string>
     */
    public function getRestrictedFacetKeys(): array
    {
        return static::RESTRICTED_TO_USE_AS_FACET_FIELD_NAMES;
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
        return static::ATTRIBUTES_TO_HIGHLIGHT_FIELDS;
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
            static::ATTRIBUTE_NAME_SKU,
            static::ATTRIBUTE_NAME_PRODUCT_ABSTRACT_SKU,
            static::ATTRIBUTE_NAME_NAME,
            static::ATTRIBUTE_NAME_ABSTRACT_NAME,
            static::ATTRIBUTE_NAME_CATEGORY,
            static::ATTRIBUTE_NAME_KEYWORDS,
            static::ATTRIBUTE_NAME_BRAND,
            static::ATTRIBUTE_NAME_DESCRIPTION,
        ];
    }

    /**
     * Specification:
     * - Returns the list of filterable attribute names extracted from filterable attributes.
     * - Excludes non-display attributes from the result.
     * - Extracts attribute names from the filterable attributes format.
     *
     * @api
     *
     * @return array<string>
     */
    public function getFilterableNameAttributes(AlgoliaConfigTransfer $algoliaConfigTransfer): array
    {
        return array_values(array_filter(array_map(
            function (string $attribute): string {
                if (in_array($attribute, $this->getNonDisplayAttributes(), true)) {
                    return '';
                }
                preg_match('/\((?<attr>[^()]+)\)/', $attribute, $matches);

                return $matches['attr'] ?? '';
            },
            $this->getFilterableAttributes($algoliaConfigTransfer),
        )));
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
    public function getFilterableAttributes(AlgoliaConfigTransfer $algoliaConfigTransfer): array
    {
        $attributes = [
            sprintf(static::SEARCHABLE_PARAM_TEMPLATE, static::ATTRIBUTE_NAME_CATEGORY),
            static::ATTRIBUTE_NAME_RATING,
            static::ATTRIBUTE_NAME_LABEL,
            static::ATTRIBUTE_NAME_COLOR,
            static::ATTRIBUTE_NAME_BRAND,
            static::ATTRIBUTE_NAME_MERCHANT_NAME,
        ];
        if ($algoliaConfigTransfer->getIsProductPriceSynced()) {
            $attributes[] = static::ATTRIBUTE_NAME_PRICES;
        }

        $result = [];

        foreach ($attributes as $attribute) {
            $result[] = sprintf(static::AFTER_DISTINCT_PARAM_TEMPLATE, $attribute);
        }

        $result = array_merge($result, $this->getNonDisplayAttributes());

        return $result;
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
        return [static::INDEXED_PRODUCT_FIELD_NAME_HIERARCHICAL_CATEGORIES];
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
     * - Returns the sorting attributes available for CMS pages.
     * - These attributes can be used to create replica indices for different sort orders.
     *
     * @api
     *
     * @return array<string>
     */
    public function getCmsPageSortingAttributes(): array
    {
        return [AlgoliaCmsPageObjectEnum::NAME->value];
    }
}
