<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Handler;

use Algolia\AlgoliaSearch\Api\SearchClient;

interface SuggestionIndexHandlerInterface
{
    public function createProductSuggestionsIndex(string $sourceIndex, SearchClient $searchClient): void;

    public function getAllConfigurations(SearchClient $searchClient): array;
}
