<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Api\Client;

use Algolia\AlgoliaSearch\SearchIndex;
use Generated\Shared\Transfer\AlgoliaResponseTransfer;
use Generated\Shared\Transfer\AlgoliaSearchResponseTransfer;

interface SearchIndexClientInterface
{
    /**
     * @param array<array<string, mixed>> $algoliaObjectTransfers
     *
     * @return \Generated\Shared\Transfer\AlgoliaResponseTransfer
     */
    public function saveObjects(array $algoliaObjectTransfers): AlgoliaResponseTransfer;

    /**
     * @param array<string> $objectIds
     *
     * @return \Generated\Shared\Transfer\AlgoliaResponseTransfer
     */
    public function deleteObjects(array $objectIds): AlgoliaResponseTransfer;

    /**
     * @param string $query
     * @param array<string, mixed> $searchParameters
     *
     * @return \Generated\Shared\Transfer\AlgoliaSearchResponseTransfer
     */
    public function search(string $query, array $searchParameters): AlgoliaSearchResponseTransfer;

    /**
     * @return bool
     */
    public function indexExists(): bool;

    /**
     * @return string
     */
    public function getIndexName(): string;

    /**
     * @return array
     */
    public function getSettings(): array;

    /**
     * @param array<string, mixed> $settings
     *
     * @return \Generated\Shared\Transfer\AlgoliaResponseTransfer
     */
    public function setSettings(array $settings): AlgoliaResponseTransfer;

    /**
     * @return \Algolia\AlgoliaSearch\SearchIndex
     */
    public function getSearchIndex(): SearchIndex;
}
