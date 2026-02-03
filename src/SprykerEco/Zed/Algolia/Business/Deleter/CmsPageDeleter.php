<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Deleter;

use Generated\Shared\Transfer\IndexConfigurationTransfer;
use Spryker\Shared\Log\LoggerTrait;
use SprykerEco\Shared\Algolia\Enum\AlgoliaEntityNameEnum;
use SprykerEco\Zed\Algolia\Business\Api\Creator\SearchClientCreatorInterface;
use SprykerEco\Zed\Algolia\Business\Api\Creator\SearchIndexClientCreatorInterface;
use SprykerEco\Zed\Algolia\Business\Api\IndexReader\IndexReaderInterface;
use SprykerEco\Zed\Algolia\Business\Config\AlgoliaConfigResolverInterface;
use SprykerEco\Zed\Algolia\Business\IndexResolver\IndexNameResolverInterface;

class CmsPageDeleter implements CmsPageDeleterInterface
{
    use LoggerTrait;

    public function __construct(
        protected SearchClientCreatorInterface $searchClientCreator,
        protected SearchIndexClientCreatorInterface $searchIndexClientCreator,
        protected IndexReaderInterface $indexReader,
        protected IndexNameResolverInterface $indexNameResolver,
        protected AlgoliaConfigResolverInterface $algoliaConfigResolver
    ) {
    }

    /**
     * @param array<int> $cmsPageIds
     */
    public function deleteCmsPagesByIds(array $cmsPageIds): void
    {
        if ($cmsPageIds === []) {
            return;
        }

        $algoliaConfigTransfer = $this->algoliaConfigResolver->getConfig();
        $searchClient = $this->searchClientCreator->createSearchClientFromConfig($algoliaConfigTransfer);

        $algoliaIndicesCollectionTransfer = $this->indexNameResolver->filterIndicesByIndexNameParts(
            $this->indexReader->getIndices($searchClient),
            $algoliaConfigTransfer->getTenantIdentifier(),
            AlgoliaEntityNameEnum::CMS_PAGE->value,
        );

        $searchIndexClients = [];
        foreach ($algoliaIndicesCollectionTransfer->getIndices() as $index) {
            // Skip replica indices - they cannot be modified directly
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
            $searchIndexClient->deleteObjects(array_map('strval', $cmsPageIds));
        }
    }
}
