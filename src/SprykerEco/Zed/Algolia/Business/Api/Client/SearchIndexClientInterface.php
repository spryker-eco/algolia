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
     */
    public function saveObjects(array $algoliaObjectTransfers): AlgoliaResponseTransfer;

    /**
     * @param array<string> $objectIds
     */
    public function deleteObjects(array $objectIds): AlgoliaResponseTransfer;

    /**
     * @param array<string, mixed> $searchParameters
     */
    public function search(string $query, array $searchParameters): AlgoliaSearchResponseTransfer;

    public function indexExists(): bool;

    public function getIndexName(): string;

    public function getSettings(): array;

    /**
     * @param array<string, mixed> $settings
     */
    public function setSettings(array $settings): AlgoliaResponseTransfer;

    public function getSearchIndex(): SearchIndex;
}
