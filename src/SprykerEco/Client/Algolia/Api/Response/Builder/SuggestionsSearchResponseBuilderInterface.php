<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Algolia\Api\Response\Builder;

use Generated\Shared\Transfer\AlgoliaSearchResponseTransfer;
use Generated\Shared\Transfer\SearchRequestTransfer;
use Generated\Shared\Transfer\SuggestionsSearchResponseTransfer;

interface SuggestionsSearchResponseBuilderInterface
{
    /**
     * @return \Generated\Shared\Transfer\SuggestionsSearchResponseTransfer
     */
    public function buildSuccessfulResponse(
        AlgoliaSearchResponseTransfer $algoliaSearchResponseTransfer,
        SearchRequestTransfer $searchRequestTransfer
    ): SuggestionsSearchResponseTransfer;

    /**
     * @return \Generated\Shared\Transfer\SuggestionsSearchResponseTransfer
     */
    public function buildUnsuccessfulResponse(string $errorMessage, int $statusCode): SuggestionsSearchResponseTransfer;
}
