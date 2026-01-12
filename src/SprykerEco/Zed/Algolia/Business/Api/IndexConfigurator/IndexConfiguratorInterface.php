<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Api\IndexConfigurator;

use Algolia\AlgoliaSearch\SearchClient;
use Algolia\AlgoliaSearch\SearchIndex;
use Generated\Shared\Transfer\AlgoliaConfigTransfer;

interface IndexConfiguratorInterface
{
    public function configureIndex(SearchIndex $index, SearchClient $searchClient, string $locale, AlgoliaConfigTransfer $algoliaConfigTransfer): void;

    /**
     * @return array<string, mixed>
     */
    public function getSettings(string $locale, AlgoliaConfigTransfer $algoliaConfigTransfer): array;

    /**
     * @return array
     */
    public function getRequestOptions(): array;

    /**
     * @return array<array>
     */
    public function getReplicaNameWithRankingAttributes(string $indexName, string $attributeName): array;
}
