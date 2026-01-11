<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Api\IndexReader;

use Algolia\AlgoliaSearch\SearchClient;
use Exception;
use Generated\Shared\Transfer\AlgoliaIndicesCollectionTransfer;

class IndexReader implements IndexReaderInterface
{
    /**
     * @var \SprykerEco\Zed\Algolia\Business\Api\IndexReader\IndexMapper
     */
    protected $indexMapper;

    /**
     * @param \SprykerEco\Zed\Algolia\Business\Api\IndexReader\IndexMapper $indexMapper
     */
    public function __construct(IndexMapper $indexMapper)
    {
        $this->indexMapper = $indexMapper;
    }

    /**
     * @param \Algolia\AlgoliaSearch\SearchClient $searchClient
     *
     * @return \Generated\Shared\Transfer\AlgoliaIndicesCollectionTransfer
     */
    public function getIndices(SearchClient $searchClient): AlgoliaIndicesCollectionTransfer
    {
        $algoliaIndicesCollectionTransfer = new AlgoliaIndicesCollectionTransfer();

        try {
            $response = $searchClient->listIndices();
        } catch (Exception $e) {
            return $algoliaIndicesCollectionTransfer;
        }

        if (!$response) {
            return $algoliaIndicesCollectionTransfer;
        }

        return $this->indexMapper->mapAlgoliaListIndicesResponseToAlgoliaIndicesCollectionTransfer(
            $response,
            $algoliaIndicesCollectionTransfer,
        );
    }
}
