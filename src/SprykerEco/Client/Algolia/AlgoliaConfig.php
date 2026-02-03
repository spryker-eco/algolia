<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Algolia;

use Generated\Shared\Transfer\FacetCollectionTransfer;
use Spryker\Client\Kernel\AbstractBundleConfig;
use SprykerEco\Shared\Algolia\AlgoliaConfig as AlgoliaAlgoliaConfig;
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
     * Strategy key for HTTP search implementation.
     *
     * @uses \Spryker\Shared\SearchHttp\SearchHttpConfig::TYPE_SEARCH_HTTP
     */
    public const string TYPE_SEARCH_HTTP = 'TYPE_SEARCH_HTTP';

    public const string TYPE_SUGGESTION_SEARCH_HTTP = 'TYPE_SUGGESTION_SEARCH_HTTP';

    public const string TYPE_PRODUCT_CONCRETE_SEARCH_HTTP = 'TYPE_PRODUCT_CONCRETE_SEARCH_HTTP';

    public const string SOURCE_IDENTIFIER_PRODUCT = 'product';

    public const string SOURCE_IDENTIFIER_CMS_PAGE = 'cms-page';

    /**
     * Used in Algolia API as UserAgent
     *
     * @var string
     */
    public const USER_AGENT_SEGMENT_NAME = 'spryker-integration';

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
    public const INDEXED_PRODUCT_FIELD_NAME_PRODUCT_ABSTRACT_SKU = 'product_abstract_sku';

    /**
     * @var string
     */
    public const INDEXED_PRODUCT_FIELD_NAME_SKU = 'sku';

    /**
     * @var string
     */
    public const INDEXED_PRODUCT_FIELD_NAME_NAME = 'name';

    /**
     * @var string
     */
    public const INDEXED_PRODUCT_FIELD_NAME_DESCRIPTION = 'description';

    /**
     * @var string
     */
    public const INDEXED_PRODUCT_FIELD_NAME_KEYWORDS = 'keywords';

    /**
     * @var string
     */
    public const INDEXED_PRODUCT_FIELD_NAME_ABSTRACT_NAME = 'abstract_name';

    /**
     * @var string
     */
    public const INDEXED_PRODUCT_FIELD_NAME_MERCHANT_NAME = 'merchant_name';

    /**
     * @var string
     */
    public const INDEXED_PRODUCT_FIELD_NAME_MERCHANT_REFERENCE = 'merchant_reference';

    /**
     * @var string
     */
    public const INDEXED_PRODUCT_FIELD_NAME_CATEGORY = 'category';

    /**
     * @var string
     */
    public const INDEXED_PRODUCT_FIELD_NAME_HIERARCHICAL_CATEGORIES = 'hierarchicalCategories';

    /**
     * @var string
     */
    public const INDEXED_PRODUCT_FIELD_NAME_IMAGES = 'images';

    /**
     * @var string
     */
    public const INDEXED_PRODUCT_FIELD_NAME_LABEL = 'label';

    /**
     * @var string
     */
    public const INDEXED_PRODUCT_FIELD_NAME_PRICES = 'prices';

    /**
     * @var string
     */
    public const INDEXED_PRODUCT_FIELD_NAME_RATING = 'rating';

    /**
     * @var string
     */
    public const INDEXED_PRODUCT_FIELD_NAME_URL = 'url';

    /**
     * @var string
     */
    public const INDEXED_PRODUCT_FIELD_NAME_ATTRIBUTES = 'attributes';

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

    public const FEATURE_PERSONALIZATION = 'personalization';

    public const CMS_PAGE_PARAMETERS = [
        'attributesToSnippet' => ['content:50'],
        'snippetEllipsisText' => '...',
        'attributesToHighlight' => ['name'],
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
     */
    public function getEntityToIndexMappings(): array
    {
        return $this->getSharedConfig()->getEntityToIndexMappings();
    }

    /**
     * Specification:
     * - Returns the suffix used for query suggestions index names.
     * - Used to distinguish query suggestion indices from main product indices.
     *
     * @api
     */
    public function getQuerySuggestionsSuffix(): string
    {
        return AlgoliaAlgoliaConfig::QUERY_SUGGESTIONS_SUFFIX;
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
     * - Returns the list of filterable attribute names extracted from filterable attributes.
     * - Excludes non-display attributes from the result.
     * - Extracts attribute names from the filterable attributes format.
     *
     * @api
     *
     * @return array<string>
     */
    public function getFilterableNameAttributes(): array
    {
        return array_values(array_filter(array_map(
            function (string $attribute): string {
                if (in_array($attribute, $this->getNonDisplayAttributes(), true)) {
                    return '';
                }
                preg_match('/\((?<attr>[^()]+)\)/', $attribute, $matches);

                return $matches['attr'] ?? '';
            },
            $this->getFilterableAttributes(),
        )));
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
            sprintf('searchable(%s)', static::ATTRIBUTE_NAME_CATEGORY),
            static::ATTRIBUTE_NAME_RATING,
            static::ATTRIBUTE_NAME_LABEL,
            static::ATTRIBUTE_NAME_COLOR,
            static::ATTRIBUTE_NAME_BRAND,
            static::ATTRIBUTE_NAME_MERCHANT_NAME,
        ];
        if ($this->getIsProductPriceSynced()) {
            $attributes[] = static::ATTRIBUTE_NAME_PRICES;
        }

        $result = [];
        foreach ($attributes as $attribute) {
            $result[] = sprintf('afterDistinct(%s)', $attribute);
        }

        return array_merge($result, $this->getNonDisplayAttributes());
    }
}
