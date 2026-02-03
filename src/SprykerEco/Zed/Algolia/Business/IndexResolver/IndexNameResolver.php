<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\IndexResolver;

use Generated\Shared\Transfer\AlgoliaIndicesCollectionTransfer;
use SprykerEco\Shared\Algolia\Enum\AlgoliaEntityNameEnum;
use SprykerEco\Zed\Algolia\AlgoliaConfig;

class IndexNameResolver implements IndexNameResolverInterface
{
    /**
     * @var string
     */
    protected const INDEX_NAME_TEMPLATE = '%s-%s-%s-%s';

    /**
     * @var string
     */
    protected const CMS_PAGE_INDEX_NAME_TEMPLATE = '%s-%s-%s';

    /**
     * @var \SprykerEco\Zed\Algolia\AlgoliaConfig
     */
    protected $algoliaConfig;

    public function __construct(AlgoliaConfig $algoliaConfig)
    {
        $this->algoliaConfig = $algoliaConfig;
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

    /**
     * @param string|null $entityName
     * @param string|null $storeName
     */
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
            static::INDEX_NAME_TEMPLATE,
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
            static::CMS_PAGE_INDEX_NAME_TEMPLATE,
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
            $this->algoliaConfig->getQuerySuggestionsSuffix(),
        );
    }
}
