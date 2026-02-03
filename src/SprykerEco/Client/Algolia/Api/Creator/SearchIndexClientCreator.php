<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Algolia\Api\Creator;

use Algolia\AlgoliaSearch\SearchClient;
use Generated\Shared\Transfer\IndexConfigurationTransfer;
use SprykerEco\Client\Algolia\Api\Client\SearchIndexClient;
use SprykerEco\Client\Algolia\Api\Client\SearchIndexClientInterface;

class SearchIndexClientCreator implements SearchIndexClientCreatorInterface
{
    public function __construct()
    {
    }

    public function createSearchIndexApiClientForSearch(
        SearchClient $client,
        IndexConfigurationTransfer $indexConfigurationTransfer
    ): SearchIndexClientInterface {
        $index = $client->initIndex($indexConfigurationTransfer->getIndexNameOrFail());

        return new SearchIndexClient($index);
    }
}
