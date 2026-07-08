<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Api\IndexConfigurator;

use Algolia\AlgoliaSearch\Api\SearchClient;
use Generated\Shared\Transfer\AlgoliaConfigTransfer;

interface IndexConfiguratorInterface
{
    public function configureIndex(string $indexName, SearchClient $searchClient, string $locale, AlgoliaConfigTransfer $algoliaConfigTransfer): void;

    /**
     * @return array<string, mixed>
     */
    public function getSettings(string $locale): array;

    /**
     * @return array<array>
     */
    public function getReplicaNameWithRankingAttributes(string $indexName, string $attributeName): array;
}
