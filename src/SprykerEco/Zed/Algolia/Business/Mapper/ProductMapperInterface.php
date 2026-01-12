<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Mapper;

use Generated\Shared\Transfer\AlgoliaConfigTransfer;
use Generated\Shared\Transfer\ProductConcreteTransfer;

interface ProductMapperInterface
{
    /**
     * Specification:
     * - Map `ProductConcrete` transfer to an associative array of `AlgoliaProduct` transfer indexed by store name and locale name.
     * - Appends the mapped data into the `$indexedAlgoliaProductTransfersArray` argument and then returns the result.
     *
     * @example The format of returned array is:
     *  [
     *      'DE' => [
     *          'en_US' => {AlgoliaProductTransfer},
     *          'de_DE' => {AlgoliaProductTransfer},
     *      ],
     *  ]
     *
     * @param array<string, array<string, array<int, \Generated\Shared\Transfer\AlgoliaProductTransfer>>> $indexedAlgoliaProductTransfersArray
     *
     * @return array<string, array<string, array<int, \Generated\Shared\Transfer\AlgoliaProductTransfer>>>
     */
    public function mapProductConcreteToAlgoliaProductTransfersArrayIndexedByStoreAndLocale(
        ProductConcreteTransfer $productConcreteTransfer,
        array $indexedAlgoliaProductTransfersArray
    ): array;

    /**
     * Specification:
     * - Map AlgoliaProductTransfer to simple array following the Algolia dataset format
     * - Replace AlgoliaProductTransfer.object.object_id with `objectID` field in the resulting array
     * - Exclude Price from data if configuration uses product without prices
     *
     * @param array<\Generated\Shared\Transfer\AlgoliaProductTransfer> $algoliaProductTransfers

     * @return array<array<string, mixed>>
     */
    public function mapAlgoliaProductTransfersArrayToAlgoliaObjectArray(array $algoliaProductTransfers, AlgoliaConfigTransfer $algoliaConfigTransfer): array;
}
