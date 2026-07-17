<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerEco\Shared\Algolia\Enum;

enum AlgoliaProductObjectEnum: string
{
    case OBJECT_ID = 'objectID';
    case SKU = 'sku';
    case PRODUCT_ABSTRACT_SKU = 'product_abstract_sku';
    case NAME = 'name';
    case ABSTRACT_NAME = 'abstract_name';
    case CATEGORY = 'category';
    case KEYWORDS = 'keywords';
    case DESCRIPTION = 'description';
    case URL = 'url';
    case RATING = 'rating';
    case LABEL = 'label';
    case PRICES = 'prices';
    case CONCRETE_PRICES = 'concrete_prices';
    case MERCHANT_NAME = 'merchant_name';
    case MERCHANT_REFERENCE = 'merchant_reference';
    case IMAGES = 'images';
    case ATTRIBUTES = 'attributes';
    case HIERARCHICAL_CATEGORIES = 'hierarchical_categories';
    case SEARCH_METADATA = 'search_metadata';
}
