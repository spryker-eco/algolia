<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Algolia;

use Generated\Shared\Transfer\AlgoliaConfigTransfer;
use Generated\Shared\Transfer\FacetCollectionTransfer;
use Spryker\Zed\Kernel\AbstractBundleConfig;
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
     * @api
     *
     * @return array<string>
     */
    public function getRestrictedFacetKeys(): array
    {
        return static::RESTRICTED_TO_USE_AS_FACET_FIELD_NAMES;
    }

    /**
     * @api
     *
     * @return array<string>
     */
    public function getAttributesToHighlight(): array
    {
        return static::ATTRIBUTES_TO_HIGHLIGHT_FIELDS;
    }

    /**
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
     * @api
     *
     * @return array<string>
     */
    public function getNonDisplayAttributes(): array
    {
        return [static::INDEXED_PRODUCT_FIELD_NAME_HIERARCHICAL_CATEGORIES];
    }

    /**
     * Returns the searchable attributes for CMS pages.
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
     * Returns the custom ranking attributes for CMS pages.
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
     * @api
     *
     * @return array<string>
     */
    public function getCmsPageSortingAttributes(): array
    {
        return [AlgoliaCmsPageObjectEnum::NAME->value];
    }

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
    public function getAdminApiKey(): string
    {
        return $this->getSharedConfig()->getAdminApiKey();
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
    public function getIsProductPriceSynced(): bool
    {
        return $this->getSharedConfig()->getIsProductPriceSynced();
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

    /**
     * Specification:
     * - Returns the default chunk size for entity export operations.
     * - This value is used when no chunk size is specified in the console command.
     *
     * @api
     *
     * @return int
     */
    public function getDefaultExportChunkSize(): int
    {
        return 100;
    }
}
