<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Algolia\Api\SearchParameters;

use Generated\Shared\Transfer\AlgoliaConfigTransfer;
use Generated\Shared\Transfer\SearchRequestTransfer;

interface SearchParametersResolverInterface
{
    /**
     * @return array<string, mixed>
     */
    public function getSearchParameters(SearchRequestTransfer $searchRequestTransfer, AlgoliaConfigTransfer $algoliaConfigTransfer): array;
}
