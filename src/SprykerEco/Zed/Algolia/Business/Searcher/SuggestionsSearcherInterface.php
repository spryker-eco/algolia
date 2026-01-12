<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Algolia\Business\Searcher;

use Generated\Shared\Transfer\SearchRequestTransfer;
use Generated\Shared\Transfer\SuggestionsSearchResponseTransfer;

interface SuggestionsSearcherInterface
{
    public function searchSuggestions(SearchRequestTransfer $searchRequestTransfer): SuggestionsSearchResponseTransfer;
}
