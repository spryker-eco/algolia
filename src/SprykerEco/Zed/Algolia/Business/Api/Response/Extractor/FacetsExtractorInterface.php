<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Api\Response\Extractor;

use Generated\Shared\Transfer\AlgoliaSearchResponseTransfer;
use Generated\Shared\Transfer\SearchRequestTransfer;

interface FacetsExtractorInterface
{
    /**
     * @return array<string, mixed>
     */
    public function extract(AlgoliaSearchResponseTransfer $algoliaSearchResponseTransfer, SearchRequestTransfer $searchRequestTransfer): array;
}
