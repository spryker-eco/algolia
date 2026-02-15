<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Client\Algolia\IndexResolver;

use Generated\Shared\Transfer\AlgoliaConfigTransfer;
use Generated\Shared\Transfer\SearchRequestTransfer;
use SprykerEco\Client\Algolia\Api\Client\SearchIndexClientInterface;

interface SearchIndexResolverInterface
{
    public function getSearchIndexClientForSearchRequest(
        SearchRequestTransfer $searchRequestTransfer,
        AlgoliaConfigTransfer $algoliaConfigTransfer
    ): SearchIndexClientInterface;

    public function getSearchIndexClientWithPrimarySearchIndex(
        SearchRequestTransfer $searchRequestTransfer,
        AlgoliaConfigTransfer $algoliaConfigTransfer
    ): SearchIndexClientInterface;
}
