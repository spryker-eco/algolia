<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\IndexResolver;

use Generated\Shared\Transfer\AlgoliaIndicesCollectionTransfer;

interface IndexNameResolverInterface
{
    public function resolveProductIndexName(
        string $tenantIdentifier,
        string $storeName,
        string $locale
    ): string;

    public function resolveCmsPageIndexName(
        string $locale,
        string $tenantIdentifier
    ): string;

    public function filterIndicesByIndexNameParts(
        AlgoliaIndicesCollectionTransfer $algoliaIndicesCollectionTransfer,
        string $tenantIdentifier,
        ?string $entityName = null,
        ?string $storeName = null
    ): AlgoliaIndicesCollectionTransfer;
}
