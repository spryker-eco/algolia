<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Algolia\Resolver;

use ArrayObject;
use Generated\Shared\Transfer\AlgoliaConfigTransfer;
use SprykerEco\Client\Algolia\AlgoliaConfig;

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
            ->setSearchOnlyApiKey($this->algoliaConfig->getSearchOnlyApiKey())
            ->setIsProductPriceSynced($this->algoliaConfig->getIsProductPriceSynced())
            ->setIsPersonalizationEnabled($this->algoliaConfig->isPersonalizationEnabled())
            ->setIsSearchInFrontendEnabledForProducts($this->algoliaConfig->isSearchInFrontendEnabledForProducts())
            ->setIsSearchInFrontendEnabledForCmsPages($this->algoliaConfig->isSearchInFrontendEnabledForCmsPages())
            ->setIsIndexMappingEnabled($this->algoliaConfig->getEntityToIndexMappings() !== [])
            ->setEntityToIndexMappings(new ArrayObject($this->algoliaConfig->getEntityToIndexMappings()));
    }
}
