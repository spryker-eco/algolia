<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Api\SearchParameters\Pagination;

use Generated\Shared\Transfer\PaginationEntryTransfer;

interface PaginationConverterInterface
{
    /**
     * @return array<string, string|int>
     */
    public function convertPaginationTransferToAlgoliaPaginationArray(PaginationEntryTransfer $paginationEntryTransfer): array;
}
