<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Algolia\Api\Client;

use Generated\Shared\Transfer\AlgoliaSearchParametersTransfer;
use Generated\Shared\Transfer\AlgoliaSearchResponseTransfer;

interface SearchIndexClientInterface
{
    public function search(string $query, AlgoliaSearchParametersTransfer $algoliaSearchParametersTransfer): AlgoliaSearchResponseTransfer;

    public function getSettings(): array;
}
