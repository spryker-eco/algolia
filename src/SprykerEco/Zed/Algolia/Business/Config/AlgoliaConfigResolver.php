<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Config;

use Generated\Shared\Transfer\AlgoliaConfigTransfer;
use SprykerEco\Zed\Algolia\AlgoliaConfig;

class AlgoliaConfigResolver implements AlgoliaConfigResolverInterface
{
    public function __construct(protected AlgoliaConfig $algoliaConfig)
    {
    }

    public function getConfig(): AlgoliaConfigTransfer
    {
        return (new AlgoliaConfigTransfer())
            ->setIsActive($this->algoliaConfig->getIsActive())
            ->setTenantIdentifier($this->algoliaConfig->getTenantIdentifier())
            ->setApplicationId($this->algoliaConfig->getApplicationId())
            ->setAdminApiKey($this->algoliaConfig->getAdminApiKey())
            ->setSearchOnlyApiKey($this->algoliaConfig->getSearchOnlyApiKey())
            ->setIsProductPriceSynced($this->algoliaConfig->getIsProductPriceSynced());
    }
}
