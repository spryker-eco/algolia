<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Shared\Algolia;

/**
 * Declares global environment configuration keys. Do not use it for other class constants.
 */
interface AlgoliaConstants
{
    /**
     * Specification:
     * - Defines the tenant identifier for Algolia.
     *
     * @api
     *
     * @var string
     */
    public const TENANT_IDENTIFIER = 'ALGOLIA:TENANT_IDENTIFIER';

    /**
     * Specification:
     * - Defines the application ID for Algolia.
     *
     * @api
     *
     * @var string
     */
    public const APPLICATION_ID = 'ALGOLIA:APPLICATION_ID';

    /**
     * Specification:
     * - Defines the admin API key for Algolia.
     *
     * @api
     *
     * @var string
     */
    public const ADMIN_API_KEY = 'ALGOLIA:ADMIN_API_KEY';

    /**
     * Specification:
     * - Defines the search-only API key for Algolia.
     *
     * @api
     *
     * @var string
     */
    public const SEARCH_ONLY_API_KEY = 'ALGOLIA:SEARCH_ONLY_API_KEY';

    /**
     * Specification:
     * - Defines whether Algolia search is enabled for products in the frontend.
     *
     * @api
     *
     * @var string
     */
    public const IS_SEARCH_IN_FRONTEND_ENABLED_FOR_PRODUCTS = 'ALGOLIA:IS_SEARCH_IN_FRONTEND_ENABLED_FOR_PRODUCTS';

    /**
     * Specification:
     * - Defines whether products without price should be indexed.
     *
     * @api
     *
     * @var string
     */
    public const IS_PRODUCT_PRICE_SYNCED = 'ALGOLIA:IS_PRODUCT_PRICE_SYNCED';

    /**
     * Specification:
     * - Defines whether Algolia search is enabled for CMS pages in the frontend.
     *
     * @api
     *
     * @var string
     */
    public const IS_SEARCH_IN_FRONTEND_ENABLED_FOR_CMS_PAGES = 'ALGOLIA:IS_SEARCH_IN_FRONTEND_ENABLED_FOR_CMS_PAGES';

    /**
     * Specification:
     * - Defines whether Algolia index mapping is enabled.
     *
     * @api
     *
     * @var string
     */
    public const ENTITY_TO_INDEX_MAPPING = 'ALGOLIA:ENTITY_TO_INDEX_MAPPING';
}
