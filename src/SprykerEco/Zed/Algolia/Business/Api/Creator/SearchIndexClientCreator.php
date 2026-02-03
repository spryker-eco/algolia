<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Api\Creator;

use Algolia\AlgoliaSearch\SearchClient;
use Generated\Shared\Transfer\IndexConfigurationTransfer;
use SprykerEco\Zed\Algolia\Business\Api\Client\SearchIndexClient;
use SprykerEco\Zed\Algolia\Business\Api\Client\SearchIndexClientInterface;
use SprykerEco\Zed\Algolia\Business\Api\IndexConfigurator\IndexConfiguratorInterface;

class SearchIndexClientCreator implements SearchIndexClientCreatorInterface
{
    public function __construct(protected IndexConfiguratorInterface $indexConfigurator)
    {
    }

    public function createSearchIndexApiClient(
        SearchClient $client,
        IndexConfigurationTransfer $indexConfigurationTransfer
    ): SearchIndexClientInterface {
        $index = $client->initIndex($indexConfigurationTransfer->getIndexNameOrFail());

        if (!$index->exists() && $indexConfigurationTransfer->getLocaleOrFail()) {
            $this->indexConfigurator->configureIndex($index, $client, $indexConfigurationTransfer->getLocale(), $indexConfigurationTransfer->getAlgoliaConfigOrFail());
        }

        return new SearchIndexClient($index);
    }
}
