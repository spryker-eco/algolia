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
}
