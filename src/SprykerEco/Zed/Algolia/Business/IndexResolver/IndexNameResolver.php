<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\IndexResolver;

use Generated\Shared\Transfer\AlgoliaIndicesCollectionTransfer;
use SprykerEco\Shared\Algolia\Enum\AlgoliaEntityNameEnum;

class IndexNameResolver implements IndexNameResolverInterface
{
    protected const string QUERY_SUGGESTIONS_SUFFIX = 'query_suggestions';

    public function __construct()
    {
    }

    public function resolveProductIndexName(
        string $tenantIdentifier,
        string $storeName,
        string $locale
    ): string {
        return strtolower($this->createProductIndexFromTemplate(
            $tenantIdentifier,
            $storeName,
            $locale,
        ));
    }

    public function resolveProductsSuggestionIndexNameFromProductIndexName(string $indexName): string
    {
        return strtolower($this->createSuggestionIndexFromTemplate($indexName));
    }

    public function resolveCmsPageIndexName(
        string $locale,
        string $tenantIdentifier
    ): string {
        return strtolower($this->createCmsPageIndexFromTemplate(
            $tenantIdentifier,
            $locale,
        ));
    }

    public function filterIndicesByIndexNameParts(
        AlgoliaIndicesCollectionTransfer $algoliaIndicesCollectionTransfer,
        string $tenantIdentifier,
        ?string $entityName = null,
        ?string $storeName = null
    ): AlgoliaIndicesCollectionTransfer {
        $tenantIdentifier = strtolower($tenantIdentifier);

        $filteredAlgoliaIndicesCollectionTransfer = new AlgoliaIndicesCollectionTransfer();

        foreach ($algoliaIndicesCollectionTransfer->getIndices() as $index) {
            if (!str_contains($index->getName(), $tenantIdentifier)) {
                continue;
            }

            if ($entityName && !str_contains($index->getName(), $entityName)) {
                continue;
            }

            if ($storeName && !str_contains($index->getName(), strtolower($storeName))) {
                continue;
            }

            $filteredAlgoliaIndicesCollectionTransfer->addIndex($index);
        }

        return $filteredAlgoliaIndicesCollectionTransfer;
    }

    protected function createProductIndexFromTemplate(
        string $tenantIdentifier,
        string $storeName,
        string $locale
    ): string {
        return sprintf(
            '%s-%s-%s-%s',
            $tenantIdentifier,
            AlgoliaEntityNameEnum::PRODUCT->value,
            $storeName,
            $locale,
        );
    }

    protected function createCmsPageIndexFromTemplate(
        string $tenantIdentifier,
        string $locale
    ): string {
        return sprintf(
            '%s-%s-%s',
            $tenantIdentifier,
            AlgoliaEntityNameEnum::CMS_PAGE->value,
            $locale,
        );
    }

    protected function createSuggestionIndexFromTemplate(string $indexName): string
    {
        return sprintf(
            '%s_%s',
            $indexName,
            static::QUERY_SUGGESTIONS_SUFFIX,
        );
    }
}
