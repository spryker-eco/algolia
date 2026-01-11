<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Api\SearchParameters\Pagination;

use Generated\Shared\Transfer\PaginationEntryTransfer;

class PaginationConverter implements PaginationConverterInterface
{
    /**
     * @var string
     */
    protected const ALGOLIA_FIELD_PAGE = 'page';

    /**
     * @var string
     */
    protected const ALGOLIA_FIELD_HITS_PER_PAGE = 'hitsPerPage';

    /**
     * @var string
     */
    protected const ALGOLIA_FIELD_OFFSET = 'offset';

    /**
     * @var string
     */
    protected const ALGOLIA_FIELD_LENGTH = 'length';

    /**
     * @var int
     */
    protected const ALGOLIA_FIELD_OFFSET_DEFAULT_VALUE = 0;

    /**
     * @var int
     */
    protected const ALGOLIA_DEFAULT_HITS_COUNT = 20;

    /**
     * @param \Generated\Shared\Transfer\PaginationEntryTransfer $paginationEntryTransfer
     *
     * @return array<string, string|int>
     */
    public function convertPaginationTransferToAlgoliaPaginationArray(PaginationEntryTransfer $paginationEntryTransfer): array
    {
        if ($paginationEntryTransfer->getPage()) {
            return [
                static::ALGOLIA_FIELD_PAGE => $paginationEntryTransfer->getPage() - 1,
                static::ALGOLIA_FIELD_HITS_PER_PAGE => $paginationEntryTransfer->getHitsPerPage() ?? static::ALGOLIA_DEFAULT_HITS_COUNT,
            ];
        }

        return [
            static::ALGOLIA_FIELD_OFFSET => $paginationEntryTransfer->getOffset() ?? static::ALGOLIA_FIELD_OFFSET_DEFAULT_VALUE,
            static::ALGOLIA_FIELD_LENGTH => $paginationEntryTransfer->getLength() ?? static::ALGOLIA_DEFAULT_HITS_COUNT,
        ];
    }
}
