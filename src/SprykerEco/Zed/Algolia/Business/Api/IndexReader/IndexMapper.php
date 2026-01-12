<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Api\IndexReader;

use Generated\Shared\Transfer\AlgoliaIndexTransfer;
use Generated\Shared\Transfer\AlgoliaIndicesCollectionTransfer;

class IndexMapper implements IndexMapperInterface
{
    /**
     * @param array<string, mixed> $algoliaResponse
     */
    public function mapAlgoliaListIndicesResponseToAlgoliaIndicesCollectionTransfer(
        array $algoliaResponse,
        AlgoliaIndicesCollectionTransfer $algoliaIndicesCollectionTransfer
    ): AlgoliaIndicesCollectionTransfer {
        if (!isset($algoliaResponse['items'])) {
            return $algoliaIndicesCollectionTransfer;
        }

        foreach ($algoliaResponse['items'] as $item) {
            $algoliaIndexTransfer = (new AlgoliaIndexTransfer())->fromArray($item, true);
            $algoliaIndexTransfer->setIsPrimary(isset($item['replicas']));

            $algoliaIndicesCollectionTransfer->addIndex($algoliaIndexTransfer);
        }

        return $algoliaIndicesCollectionTransfer;
    }
}
