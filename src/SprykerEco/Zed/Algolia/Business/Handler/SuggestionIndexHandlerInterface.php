<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Handler;

use Algolia\AlgoliaSearch\SearchClient;

interface SuggestionIndexHandlerInterface
{
    /**
     * @throws \Algolia\AlgoliaSearch\Exceptions\BadRequestException
     *
     * @return void
     */
    public function createProductSuggestionsIndex(string $sourceIndex, SearchClient $searchClient): void;

    /**
     * @throws \Algolia\AlgoliaSearch\Exceptions\BadRequestException
     *
     * @return array
     */
    public function getAllConfigurations(SearchClient $searchClient): array;
}
