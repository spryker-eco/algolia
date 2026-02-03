<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Algolia\SprykerSearch\Query;

use Generated\Shared\Transfer\SearchContextTransfer;
use SprykerEco\Client\Algolia\AlgoliaConfig;

class QueryApplicabilityChecker implements QueryApplicabilityCheckerInterface
{
    public function __construct(protected AlgoliaConfig $algoliaConfig)
    {
    }

    public function isQueryApplicable(SearchContextTransfer $searchContextTransfer): bool
    {
        if (!$this->algoliaConfig->getIsActive()) {
            return false;
        }

        if ($searchContextTransfer->getSourceIdentifier() === '*') {
            return true;
        }

        if ($searchContextTransfer->getSourceIdentifier() === AlgoliaConfig::SOURCE_IDENTIFIER_PRODUCT && $this->algoliaConfig->isSearchInFrontendEnabledForProducts()) {
            return true;
        }

        if ($searchContextTransfer->getSourceIdentifier() === AlgoliaConfig::SOURCE_IDENTIFIER_CMS_PAGE && $this->algoliaConfig->isSearchInFrontendEnabledForCmsPages()) {
            return true;
        }

        return false;
    }
}
