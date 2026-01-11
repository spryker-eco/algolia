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
use SprykerEco\Zed\Algolia\Business\Api\IndexConfigurator\IndexConfigurator;

class SearchIndexClientCreator implements SearchIndexClientCreatorInterface
{
    /**
     * @var \SprykerEco\Zed\Algolia\Business\Api\IndexConfigurator\IndexConfigurator
     */
    protected $indexConfigurator;

    /**
     * @param \SprykerEco\Zed\Algolia\Business\Api\IndexConfigurator\IndexConfigurator $indexConfigurator
     */
    public function __construct(IndexConfigurator $indexConfigurator)
    {
        $this->indexConfigurator = $indexConfigurator;
    }

    /**
     * @param \Algolia\AlgoliaSearch\SearchClient $client
     * @param \Generated\Shared\Transfer\IndexConfigurationTransfer $indexConfigurationTransfer
     *
     * @return \SprykerEco\Zed\Algolia\Business\Api\Client\SearchIndexClientInterface
     */
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

    /**
     * @param \Algolia\AlgoliaSearch\SearchClient $client
     * @param \Generated\Shared\Transfer\IndexConfigurationTransfer $indexConfigurationTransfer
     *
     * @return \SprykerEco\Zed\Algolia\Business\Api\Client\SearchIndexClientInterface
     */
    public function createSearchIndexApiClientForSearch(
        SearchClient $client,
        IndexConfigurationTransfer $indexConfigurationTransfer
    ): SearchIndexClientInterface {
        $index = $client->initIndex($indexConfigurationTransfer->getIndexNameOrFail());

        return new SearchIndexClient($index);
    }
}
