<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Api\Client;

use Algolia\AlgoliaSearch\Api\SearchClient;
use Generated\Shared\Transfer\AlgoliaResponseTransfer;
use Generated\Shared\Transfer\AlgoliaSearchResponseTransfer;

class SearchIndexClient implements SearchIndexClientInterface
{
    public function __construct(
        protected SearchClient $searchClient,
        protected string $indexName,
    ) {
    }

    /**
     * @param array<array<string, mixed>> $algoliaObjectTransfers
     */
    public function saveObjects(array $algoliaObjectTransfers): AlgoliaResponseTransfer
    {
        $this->searchClient->saveObjects($this->indexName, $algoliaObjectTransfers);

        return $this->createSuccessfulAlgoliaResponseTransfer();
    }

    /**
     * @param array<string> $objectIds
     */
    public function deleteObjects(array $objectIds): AlgoliaResponseTransfer
    {
        $this->searchClient->deleteObjects($this->indexName, $objectIds);

        return $this->createSuccessfulAlgoliaResponseTransfer();
    }

    /**
     * @param array<string, mixed> $searchParameters
     */
    public function search(string $query, array $searchParameters): AlgoliaSearchResponseTransfer
    {
        $result = $this->searchClient->searchSingleIndex($this->indexName, ['query' => $query] + $searchParameters);

        return (new AlgoliaSearchResponseTransfer())
            ->setSearchResults($result)
            ->setIsSuccessful(true);
    }

    public function indexExists(): bool
    {
        return $this->searchClient->indexExists($this->indexName);
    }

    public function getIndexName(): string
    {
        return $this->indexName;
    }

    public function getSettings(): array
    {
        return $this->searchClient->getSettings($this->indexName);
    }

    /**
     * @param array<string, mixed> $settings
     */
    public function setSettings(array $settings): AlgoliaResponseTransfer
    {
        $this->searchClient->setSettings($this->indexName, $settings);

        return $this->createSuccessfulAlgoliaResponseTransfer();
    }

    protected function createSuccessfulAlgoliaResponseTransfer(): AlgoliaResponseTransfer
    {
        return (new AlgoliaResponseTransfer())
            ->setIsSuccessful(true);
    }
}
