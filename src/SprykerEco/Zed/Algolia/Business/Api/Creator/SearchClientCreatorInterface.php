<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Api\Creator;

use Algolia\AlgoliaSearch\SearchClient;
use Generated\Shared\Transfer\AlgoliaApiCredentialsTransfer;
use Generated\Shared\Transfer\AlgoliaConfigTransfer;

interface SearchClientCreatorInterface
{
    public function createSearchClient(AlgoliaApiCredentialsTransfer $algoliaCredentialsTransfer): SearchClient;

    public function createSearchClientByStoreReference(AlgoliaConfigTransfer $algoliaConfigTransfer, bool $isSearchOnly = false): SearchClient;
}
