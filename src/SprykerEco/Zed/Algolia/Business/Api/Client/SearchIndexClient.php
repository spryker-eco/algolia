<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Api\Client;

use Algolia\AlgoliaSearch\SearchIndex;
use Generated\Shared\Transfer\AlgoliaResponseTransfer;
use Generated\Shared\Transfer\AlgoliaSearchResponseTransfer;
use Spryker\Shared\Log\LoggerTrait;

class SearchIndexClient implements SearchIndexClientInterface
{
    use LoggerTrait;

    /**
     * @var \Algolia\AlgoliaSearch\SearchIndex
     */
    protected $searchIndex;

    /**
     * @param \Algolia\AlgoliaSearch\SearchIndex $searchIndex
     */
    public function __construct(SearchIndex $searchIndex)
    {
        $this->searchIndex = $searchIndex;
    }

    /**
     * @param array<array<string, mixed>> $algoliaObjectTransfers
     *
     * @return \Generated\Shared\Transfer\AlgoliaResponseTransfer
     */
    public function saveObjects(array $algoliaObjectTransfers): AlgoliaResponseTransfer
    {
        $this->searchIndex->saveObjects($algoliaObjectTransfers);

        return $this->createSuccessfulAlgoliaResponseTransfer();
    }

    /**
     * @param array<string> $objectIds
     *
     * @return \Generated\Shared\Transfer\AlgoliaResponseTransfer
     */
    public function deleteObjects(array $objectIds): AlgoliaResponseTransfer
    {
        $this->searchIndex->deleteObjects($objectIds);

        return $this->createSuccessfulAlgoliaResponseTransfer();
    }

    /**
     * @param string $query
     * @param array<string, mixed> $searchParameters
     *
     * @return \Generated\Shared\Transfer\AlgoliaSearchResponseTransfer
     */
    public function search(string $query, array $searchParameters): AlgoliaSearchResponseTransfer
    {
        $result = $this->searchIndex->search($query, $searchParameters);

        return (new AlgoliaSearchResponseTransfer())
            ->setSearchResults($result)
            ->setIsSuccessful(true);
    }

    /**
     * @return bool
     */
    public function indexExists(): bool
    {
        return $this->searchIndex->exists();
    }

    /**
     * @return string
     */
    public function getIndexName(): string
    {
        return $this->searchIndex->getIndexName();
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        return $this->searchIndex->getSettings();
    }

    /**
     * @param array<string, mixed> $settings
     *
     * @return \Generated\Shared\Transfer\AlgoliaResponseTransfer
     */
    public function setSettings(array $settings): AlgoliaResponseTransfer
    {
        $this->searchIndex->setSettings($settings);

        return $this->createSuccessfulAlgoliaResponseTransfer();
    }

    /**
     * @return \Algolia\AlgoliaSearch\SearchIndex
     */
    public function getSearchIndex(): SearchIndex
    {
        return $this->searchIndex;
    }

    /**
     * @return \Generated\Shared\Transfer\AlgoliaResponseTransfer
     */
    protected function createSuccessfulAlgoliaResponseTransfer(): AlgoliaResponseTransfer
    {
        return (new AlgoliaResponseTransfer())
            ->setIsSuccessful(true);
    }
}
