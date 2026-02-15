<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Algolia\SprykerSearch\Query;

use Generated\Shared\Transfer\SearchContextTransfer;

interface QueryApplicabilityCheckerInterface
{
    /**
     * @return bool
     */
    public function isQueryApplicable(SearchContextTransfer $searchContextTransfer): bool;
}
