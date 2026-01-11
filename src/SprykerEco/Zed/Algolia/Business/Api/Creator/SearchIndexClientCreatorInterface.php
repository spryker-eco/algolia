<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Api\Creator;

use Algolia\AlgoliaSearch\SearchClient;
use Generated\Shared\Transfer\IndexConfigurationTransfer;
use SprykerEco\Zed\Algolia\Business\Api\Client\SearchIndexClientInterface;

interface SearchIndexClientCreatorInterface
{
    /**
     * @param \Algolia\AlgoliaSearch\SearchClient $client
     * @param \Generated\Shared\Transfer\IndexConfigurationTransfer $indexConfigurationTransfer
     *
     * @return \SprykerEco\Zed\Algolia\Business\Api\Client\SearchIndexClientInterface
     */
    public function createSearchIndexApiClient(
        SearchClient $client,
        IndexConfigurationTransfer $indexConfigurationTransfer
    ): SearchIndexClientInterface;

    /**
     * @param \Algolia\AlgoliaSearch\SearchClient $client
     * @param \Generated\Shared\Transfer\IndexConfigurationTransfer $indexConfigurationTransfer
     *
     * @return \SprykerEco\Zed\Algolia\Business\Api\Client\SearchIndexClientInterface
     */
    public function createSearchIndexApiClientForSearch(
        SearchClient $client,
        IndexConfigurationTransfer $indexConfigurationTransfer
    ): SearchIndexClientInterface;
}
