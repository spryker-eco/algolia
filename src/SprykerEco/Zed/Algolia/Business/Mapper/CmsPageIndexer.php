<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerEco\Zed\Algolia\Business\Mapper;

use Exception;
use Generated\Shared\Transfer\CmsPagePublishedTransfer;
use Generated\Shared\Transfer\CmsPageTransfer;
use Spryker\Shared\Log\LoggerTrait;
use SprykerEco\Zed\Algolia\Business\IndexResolver\IndexNameResolverInterface;

class CmsPageIndexer implements CmsPageIndexerInterface
{
    use LoggerTrait;

    public function __construct(
        protected CmsPageMapperInterface $cmsPageMapper,
        protected IndexNameResolverInterface $indexNameResolver
    ) {
    }

    /**
     * @param array $flattenedLocaleCmsPageData
     *
     * @return array
     */
    public function buildIndexData(
        CmsPagePublishedTransfer $cmsPagePublishedTransfer,
        string $locale,
        string $tenantIdentifier,
        CmsPageTransfer $cmsPageTransfer,
        array $flattenedLocaleCmsPageData
    ): array {
        $indexName = $this->indexNameResolver->resolveCmsPageIndexName(
            $locale,
            $tenantIdentifier,
        );
        try {
            $cmsPageData = $this->cmsPageMapper->mapCmsPageDataToAlgoliaData($cmsPagePublishedTransfer, $locale, $cmsPageTransfer, $flattenedLocaleCmsPageData);
        } catch (Exception $exception) {
            $this->getLogger()->error($exception->getMessage());

            return [];
        }

        return [
            'indexName' => $indexName,
            'locale' => $locale,
            'tenantIdentifier' => $tenantIdentifier,
            'data' => $cmsPageData,
        ];
    }
}
