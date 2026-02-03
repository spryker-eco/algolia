<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Client\Algolia\IndexResolver;

use ArrayObject;
use Exception;
use Generated\Shared\Transfer\AlgoliaConfigTransfer;
use Generated\Shared\Transfer\IndexConfigurationTransfer;
use Generated\Shared\Transfer\SearchRequestTransfer;
use SprykerEco\Client\Algolia\Api\Client\SearchIndexClientInterface;
use SprykerEco\Client\Algolia\Api\Creator\SearchClientCreatorInterface;
use SprykerEco\Client\Algolia\Api\Creator\SearchIndexClientCreatorInterface;
use SprykerEco\Client\Algolia\Resolver\AlgoliaConfigResolverInterface;
use SprykerEco\Shared\Algolia\Enum\AlgoliaEntityNameEnum;

class SearchIndexResolver implements SearchIndexResolverInterface
{
    public function __construct(
        protected IndexNameResolverInterface $indexNameResolver,
        protected SearchClientCreatorInterface $searchClientCreator,
        protected SearchIndexClientCreatorInterface $searchIndexClientCreator,
        protected AlgoliaConfigResolverInterface $algoliaConfigResolver
    ) {
    }

    public function getSearchIndexClientForSearchRequest(
        SearchRequestTransfer $searchRequestTransfer,
        AlgoliaConfigTransfer $algoliaConfigTransfer
    ): SearchIndexClientInterface {
        $indexName = $this->resolveIndexNameBySourceIdentifier(
            $searchRequestTransfer->getSourceIdentifierOrFail(),
            $algoliaConfigTransfer->getTenantIdentifierOrFail(),
            $searchRequestTransfer->getStoreName(),
            $searchRequestTransfer->getLocale(),
            $algoliaConfigTransfer->getEntityToIndexMappings(),
        );

        if ($searchRequestTransfer->getSort() && $searchRequestTransfer->getSort()->getField()) {
            $indexName = $this->indexNameResolver->getIndexReplicaNameForSorting($indexName, $searchRequestTransfer->getSort(), $searchRequestTransfer->getFacets());
        }

        return $this->createSearchIndexClientWithIndexName($searchRequestTransfer, $indexName);
    }

    public function getSearchIndexClientWithPrimarySearchIndex(
        SearchRequestTransfer $searchRequestTransfer,
        AlgoliaConfigTransfer $algoliaConfigTransfer
    ): SearchIndexClientInterface {
        $indexName = $this->resolveIndexNameBySourceIdentifier(
            $searchRequestTransfer->getSourceIdentifierOrFail(),
            $algoliaConfigTransfer->getTenantIdentifierOrFail(),
            $searchRequestTransfer->getStoreName(),
            $searchRequestTransfer->getLocale(),
        );

        return $this->createSearchIndexClientWithIndexName($searchRequestTransfer, $indexName);
    }

    protected function createSearchIndexClientWithIndexName(
        SearchRequestTransfer $searchRequestTransfer,
        string $indexName
    ): SearchIndexClientInterface {
        $algoliaConfigTransfer = $this->algoliaConfigResolver->getConfig();
        $searchClient = $this->searchClientCreator->createSearchClientFromConfig($algoliaConfigTransfer, true);

        return $this->searchIndexClientCreator->createSearchIndexApiClientForSearch($searchClient, (new IndexConfigurationTransfer())->setIndexName($indexName));
    }

    /**
     * @param \ArrayObject<\Generated\Shared\Transfer\EntityToIndexMappingTransfer> $entityToIndexMappings
     */
    protected function resolveIndexNameBySourceIdentifier(
        string $sourceIdentifier,
        string $tenantIdentifier,
        string $storeName,
        string $locale,
        ArrayObject $entityToIndexMappings = new ArrayObject()
    ): string {
        switch ($sourceIdentifier) {
            case AlgoliaEntityNameEnum::PRODUCT->value:
                if ($entityToIndexMappings->count() === 0) {
                    return $this->indexNameResolver->resolveProductIndexName($tenantIdentifier, $storeName, $locale);
                }
                try {
                    return $this->indexNameResolver->resolveIndexNameByMapping($sourceIdentifier, $storeName, $locale, $entityToIndexMappings);
                } catch (Exception $e) {
                    return $this->indexNameResolver->resolveProductIndexName($tenantIdentifier, $storeName, $locale);
                }
            case AlgoliaEntityNameEnum::CMS_PAGE->value:
                if ($entityToIndexMappings->count() === 0) {
                    return $this->indexNameResolver->resolveCmsPageIndexName($locale, $tenantIdentifier);
                }
                try {
                    return $this->indexNameResolver->resolveIndexNameByMapping($sourceIdentifier, $storeName, $locale, $entityToIndexMappings);
                } catch (Exception $e) {
                    return $this->indexNameResolver->resolveCmsPageIndexName($locale, $tenantIdentifier);
                }

            default:
                return $this->indexNameResolver->resolveIndexNameByMapping($sourceIdentifier, $storeName, $locale, $entityToIndexMappings);
        }
    }
}
