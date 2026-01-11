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
     * @param string $sourceIndex
     * @param \Algolia\AlgoliaSearch\SearchClient $searchClient
     *
     * @throws \Algolia\AlgoliaSearch\Exceptions\BadRequestException
     *
     * @return void
     */
    public function createSuggestionsIndex(string $sourceIndex, SearchClient $searchClient): void;

    /**
     * @param \Algolia\AlgoliaSearch\SearchClient $searchClient
     *
     * @throws \Algolia\AlgoliaSearch\Exceptions\BadRequestException
     *
     * @return array
     */
    public function getAllConfigurations(SearchClient $searchClient): array;

    /**
     * @param string $configurationName
     * @param \Algolia\AlgoliaSearch\SearchClient $searchClient
     *
     * @throws \Algolia\AlgoliaSearch\Exceptions\BadRequestException
     *
     * @return void
     */
    public function deleteConfiguration(string $configurationName, SearchClient $searchClient): void;
}
