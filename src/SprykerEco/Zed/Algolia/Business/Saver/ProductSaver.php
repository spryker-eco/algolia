<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Saver;

use Generated\Shared\Transfer\AlgoliaConfigTransfer;
use Generated\Shared\Transfer\AlgoliaResponseTransfer;
use Generated\Shared\Transfer\IndexConfigurationTransfer;
use Generated\Shared\Transfer\IndexedAlgoliaProductCollectionTransfer;
use SprykerEco\Zed\Algolia\Business\Api\Client\SearchIndexClientInterface;
use SprykerEco\Zed\Algolia\Business\Api\Creator\SearchClientCreatorInterface;
use SprykerEco\Zed\Algolia\Business\Api\Creator\SearchIndexClientCreatorInterface;
use SprykerEco\Zed\Algolia\Business\Mapper\ProductMapperInterface;

class ProductSaver implements ProductSaverInterface
{
    /**
     * @var string
     */
    protected const ERROR_MESSAGE_INDEX_NAME_ERROR_TEMPLATE = 'Error happened when saving products in Algolia index %s: %s';

    /**
     * @var string
     */
    protected const ERROR_MESSAGE_INDEX_NAME_TEMPLATE = 'Error happened when saving products in Algolia index %s';

    public function __construct(
        protected SearchClientCreatorInterface $searchClientCreator,
        protected SearchIndexClientCreatorInterface $searchIndexClientCreator,
        protected ProductMapperInterface $productMapper
    ) {
    }

    /**
     * @param array<\Generated\Shared\Transfer\IndexedAlgoliaProductCollectionTransfer> $indexedAlgoliaProductCollections
     */
    public function saveAlgoliaProducts(array $indexedAlgoliaProductCollections, AlgoliaConfigTransfer $algoliaConfigTransfer): AlgoliaResponseTransfer
    {
        $algoliaResponseTransfer = (new AlgoliaResponseTransfer())
            ->setIsSuccessful(true);

        foreach ($indexedAlgoliaProductCollections as $indexedAlgoliaProductCollection) {
            $algoliaResponseTransfer = $this->saveIndexedAlgoliaProductCollection(
                $indexedAlgoliaProductCollection,
                $algoliaConfigTransfer,
                $algoliaResponseTransfer,
            );
        }

        return $algoliaResponseTransfer;
    }

    protected function saveIndexedAlgoliaProductCollection(
        IndexedAlgoliaProductCollectionTransfer $indexedAlgoliaProductCollectionTransfer,
        AlgoliaConfigTransfer $algoliaConfigTransfer,
        AlgoliaResponseTransfer $algoliaResponseTransfer
    ): AlgoliaResponseTransfer {
        $indexName = $indexedAlgoliaProductCollectionTransfer->getIndexName();
        $searchClient = $this->searchClientCreator->createSearchClientFromConfig($algoliaConfigTransfer);

        $searchIndexClient = $this->searchIndexClientCreator->createSearchIndexApiClient(
            $searchClient,
            (new IndexConfigurationTransfer())
                ->setAlgoliaConfig($algoliaConfigTransfer)
                ->setIndexName($indexName)
                ->setLocale($indexedAlgoliaProductCollectionTransfer->getLocale()),
        );

        $groupAlgoliaResponseTransfer = $this->saveObjects(
            $searchIndexClient,
            $indexedAlgoliaProductCollectionTransfer->getAlgoliaProducts()->getArrayCopy(),
            $algoliaConfigTransfer,
        );

        if (!$groupAlgoliaResponseTransfer->getIsSuccessful()) {
            $algoliaResponseTransfer
                ->setIsSuccessful(false);

            $algoliaResponseTransfer->setResponseMessage(
                $this->addIndexNameToErrorResponse(
                    $indexName,
                    $groupAlgoliaResponseTransfer->getResponseMessage(),
                ),
            );
        }

        return $algoliaResponseTransfer;
    }

    protected function addIndexNameToErrorResponse(string $indexName, ?string $errorResponse): string
    {
        if ($errorResponse) {
            return sprintf(static::ERROR_MESSAGE_INDEX_NAME_ERROR_TEMPLATE, $indexName, $errorResponse);
        }

        return sprintf(static::ERROR_MESSAGE_INDEX_NAME_TEMPLATE, $indexName);
    }

    /**
     * @param array<\Generated\Shared\Transfer\AlgoliaProductTransfer> $algoliaProductTransfers
     */
    protected function saveObjects(
        SearchIndexClientInterface $searchIndexClient,
        array $algoliaProductTransfers,
        AlgoliaConfigTransfer $algoliaConfigTransfer
    ): AlgoliaResponseTransfer {
        $algoliaObjectsArrays = $this->productMapper
            ->mapAlgoliaProductTransfersArrayToAlgoliaObjectArray($algoliaProductTransfers, $algoliaConfigTransfer);

        if (!count($algoliaObjectsArrays)) {
            return new AlgoliaResponseTransfer();
        }

        return $searchIndexClient->saveObjects($algoliaObjectsArrays);
    }
}
