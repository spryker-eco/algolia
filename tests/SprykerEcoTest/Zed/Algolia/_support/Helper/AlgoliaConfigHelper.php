<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Zed\Algolia\Helper;

use Codeception\Module;
use Generated\Shared\DataBuilder\AlgoliaConfigBuilder;
use Generated\Shared\Transfer\AlgoliaConfigTransfer;

class AlgoliaConfigHelper extends Module
{
    /**
     * @param array $seed
     */
    public function haveAlgoliaConfigTransfer(array $seed = []): AlgoliaConfigTransfer
    {
        return (new AlgoliaConfigBuilder($seed))->build();
    }

    public function haveDefaultAlgoliaConfigTransfer(): AlgoliaConfigTransfer
    {
        return (new AlgoliaConfigTransfer())->fromArray([
            AlgoliaConfigTransfer::APPLICATION_ID => 'application_id',
            AlgoliaConfigTransfer::ADMIN_API_KEY => 'admin_api_key',
            AlgoliaConfigTransfer::SEARCH_ONLY_API_KEY => 'search_only_api_key',
            AlgoliaConfigTransfer::IS_SEARCH_IN_FRONTEND_ENABLED_FOR_PRODUCTS => true,
        ]);
    }
}
