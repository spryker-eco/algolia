<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Api\SearchParameters\Filter;

use Generated\Shared\Transfer\SearchRequestTransfer;

interface FilterConverterInterface
{
    /**
     * @param \Generated\Shared\Transfer\SearchRequestTransfer $searchRequestTransfer
     *
     * @return string
     */
    public function convertFacetCollectionTransferToAlgoliaFiltersString(SearchRequestTransfer $searchRequestTransfer): string;
}
