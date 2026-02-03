<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Algolia;

use Generated\Shared\Transfer\SearchRequestTransfer;
use Generated\Shared\Transfer\SearchResponseTransfer;
use Generated\Shared\Transfer\SuggestionsSearchResponseTransfer;
use Spryker\Client\Kernel\AbstractClient;

/**
 * @method \SprykerEco\Client\Algolia\AlgoliaFactory getFactory()
 */
class AlgoliaClient extends AbstractClient implements AlgoliaClientInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function search(SearchRequestTransfer $searchRequestTransfer): SearchResponseTransfer
    {
        return $this->getFactory()->createSearcher()->search($searchRequestTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function searchSuggestions(SearchRequestTransfer $searchRequestTransfer): SuggestionsSearchResponseTransfer
    {
        return $this->getFactory()->createSuggestionsSearcher()->searchSuggestions($searchRequestTransfer);
    }
}
