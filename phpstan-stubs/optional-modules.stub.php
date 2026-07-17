<?php

/**
 * PHPStan-only reflection stubs for optional Spryker peer modules that
 * spryker-eco/algolia integrates with via class_exists() feature detection
 * in AlgoliaConfig.php (see composer.json "suggest": spryker/price-product,
 * spryker/product-label, spryker/product-review). These modules are not
 * installed in every consuming project, so PHPStan otherwise proves the
 * class_exists() checks always false and flags them as impossibleType.
 *
 * This file is only loaded via phpstan.neon's `bootstrapFiles` for static
 * analysis. It is not part of the package's composer autoload and has no
 * effect on real runtime behavior (the class_exists() guards below are a
 * no-op unless the real module happens to already be loaded first).
 */

namespace Spryker\Zed\PriceProduct\Dependency {
    if (!class_exists('Spryker\Zed\PriceProduct\Dependency\PriceProductEvents', false)) {
        class PriceProductEvents
        {
            public const PRICE_CONCRETE_PUBLISH = 'Price.concrete.publish';

            public const PRICE_ABSTRACT_PUBLISH = 'Price.abstract.publish';

            public const ENTITY_SPY_PRICE_PRODUCT_CREATE = 'Entity.spy_price_product.create';

            public const ENTITY_SPY_PRICE_PRODUCT_UPDATE = 'Entity.spy_price_product.update';
        }
    }
}

namespace Spryker\Zed\ProductLabel\Dependency {
    if (!class_exists('Spryker\Zed\ProductLabel\Dependency\ProductLabelEvents', false)) {
        class ProductLabelEvents
        {
            public const ENTITY_SPY_PRODUCT_LABEL_PRODUCT_ABSTRACT_CREATE = 'Entity.spy_product_label_product_abstract.create';

            public const ENTITY_SPY_PRODUCT_LABEL_PRODUCT_ABSTRACT_DELETE = 'Entity.spy_product_label_product_abstract.delete';
        }
    }
}

namespace Spryker\Zed\ProductReview\Dependency {
    if (!class_exists('Spryker\Zed\ProductReview\Dependency\ProductReviewEvents', false)) {
        class ProductReviewEvents
        {
            public const PRODUCT_ABSTRACT_REVIEW_PUBLISH = 'ProductAbstract.review.publish';

            public const ENTITY_SPY_PRODUCT_REVIEW_CREATE = 'Entity.spy_product_review.create';

            public const ENTITY_SPY_PRODUCT_REVIEW_UPDATE = 'Entity.spy_product_review.update';
        }
    }
}
