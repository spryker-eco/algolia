<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Indexer;

use ArrayObject;

interface ProductIndexerInterface
{
    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\ProductConcreteTransfer> $productsConcrete

     * @return array<int, \Generated\Shared\Transfer\IndexedAlgoliaProductCollectionTransfer>
     */
    public function indexProductsConcreteByStoreAndLocale(ArrayObject $productsConcrete, string $tenantIdentifier): array;
}
