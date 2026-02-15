<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Api\IndexReader;

use Algolia\AlgoliaSearch\SearchClient;
use Generated\Shared\Transfer\AlgoliaIndicesCollectionTransfer;

interface IndexReaderInterface
{
    /**
     * @return \Generated\Shared\Transfer\AlgoliaIndicesCollectionTransfer
     */
    public function getIndices(SearchClient $searchClient): AlgoliaIndicesCollectionTransfer;
}
