<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Api\Client;

use Generated\Shared\Transfer\AlgoliaResponseTransfer;

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

    public function getSettings(): array;

    /**
     * @param array<string, mixed> $settings
     */
    public function setSettings(array $settings): AlgoliaResponseTransfer;
}
