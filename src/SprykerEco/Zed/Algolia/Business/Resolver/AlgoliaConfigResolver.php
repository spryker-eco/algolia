<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Resolver;

use Generated\Shared\Transfer\AlgoliaConfigTransfer;

class AlgoliaConfigResolver implements AlgoliaConfigResolverInterface
{
    public function __construct()
    {
    }

    public function findConfig(): ?AlgoliaConfigTransfer
    {
        return (new AlgoliaConfigTransfer())
            ->setTenantIdentifier('')
            ->setAdminApiKey('')
            ->setSearchOnlyApiKey('')
            ->setApplicationId('');
    }
}
