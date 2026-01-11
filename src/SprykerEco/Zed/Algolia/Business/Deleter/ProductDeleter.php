<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Deleter;

use Generated\Shared\Transfer\AlgoliaConfigTransfer;
use Generated\Shared\Transfer\IndexConfigurationTransfer;
use Generated\Shared\Transfer\ProductDeletedTransfer;
use Spryker\Shared\Log\LoggerTrait;
use SprykerEco\Shared\Algolia\Enum\AlgoliaEntityNameEnum;
use SprykerEco\Zed\Algolia\Business\Api\Creator\SearchClientCreatorInterface;
use SprykerEco\Zed\Algolia\Business\Api\Creator\SearchIndexClientCreatorInterface;
use SprykerEco\Zed\Algolia\Business\Api\IndexReader\IndexReaderInterface;
use SprykerEco\Zed\Algolia\Business\IndexResolver\IndexNameResolver;
use SprykerEco\Zed\Algolia\Business\Resolver\AlgoliaConfigResolver;

class ProductDeleter implements ProductDeleterInterface
{
    use LoggerTrait;

    public function __construct(
        protected SearchClientCreatorInterface $searchClientCreator,
        protected SearchIndexClientCreatorInterface $searchIndexClientCreator,
        protected IndexReaderInterface $indexReader,
        protected IndexNameResolver $indexNameResolver,
        protected AlgoliaConfigResolver $algoliaConfigResolver
    ) {
    }

    /**
     * @param \Generated\Shared\Transfer\ProductDeletedTransfer $productDeletedTransfer
     *
     * @return void
     */
    public function deleteProduct(ProductDeletedTransfer $productDeletedTransfer): void
    {
        $algoliaConfigTransfer = $this->algoliaConfigResolver->findConfig();
        if ($algoliaConfigTransfer === null) {
            return;
        }

        $this->deleteProducts(
            [$productDeletedTransfer],
            $algoliaConfigTransfer,
        );
    }

    /**
     * @param array<\Generated\Shared\Transfer\ProductDeletedTransfer> $productDeletedTransfers
     * @param \Generated\Shared\Transfer\AlgoliaConfigTransfer $algoliaConfigTransfer
     * @param string|null $storeName
     *
     * @return void
     */
    public function deleteProducts(array $productDeletedTransfers, AlgoliaConfigTransfer $algoliaConfigTransfer, ?string $storeName = null): void
    {
        $searchClient = $this->searchClientCreator->createSearchClientByStoreReference($algoliaConfigTransfer);

        $algoliaIndicesCollectionTransfer = $this->indexNameResolver->filterIndicesByIndexNameParts(
            $this->indexReader->getIndices($searchClient),
            $algoliaConfigTransfer->getTenantIdentifier(),
            AlgoliaEntityNameEnum::PRODUCT->value,
            $storeName,
        );

        $skus = array_map(function (ProductDeletedTransfer $productDeletedTransfer) {
            return $productDeletedTransfer->getSku();
        }, $productDeletedTransfers);

        $searchIndexClients = [];
        foreach ($algoliaIndicesCollectionTransfer->getIndices() as $index) {
            if (!$index->getIsPrimary()) {
                continue;
            }
            if (!isset($searchIndexClients[$index->getName()])) {
                $searchIndexClients[$index->getName()] = $this->searchIndexClientCreator
                    ->createSearchIndexApiClient(
                        $searchClient,
                        (new IndexConfigurationTransfer())
                            ->setAlgoliaConfig($algoliaConfigTransfer)
                            ->setIndexName($index->getName())
                            ->setLocale(''),
                    );
            }
            $searchIndexClient = $searchIndexClients[$index->getName()];
            $searchIndexClient->deleteObjects($skus);
        }
    }
}
