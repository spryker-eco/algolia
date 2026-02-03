<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Algolia;

use Generated\Shared\Transfer\SearchRequestTransfer;
use Generated\Shared\Transfer\SearchResponseTransfer;
use Generated\Shared\Transfer\SuggestionsSearchResponseTransfer;

interface AlgoliaClientInterface
{
    /**
     * Specification:
     * - Performs a search request on Algolia app.
     * - Resolves Algolia configuration from persistent storage.
     * - Determines search index based on source identifier (product, cms_page, or custom entity).
     * - Converts search request filters and pagination to Algolia search parameters.
     * - Handles sorting by creating replica indices or falling back to primary index.
     * - Extracts products, CMS pages, facets, and pagination from Algolia response.
     * - Returns unsuccessful response with error message if index not found or request fails.
     *
     * @api
     *
     * @return \Generated\Shared\Transfer\SearchResponseTransfer
     */
    public function search(SearchRequestTransfer $searchRequestTransfer): SearchResponseTransfer;

    /**
     * Specification:
     * - Performs a multiple indices search request on Algolia app for suggestions.
     * - Resolves Algolia configuration from persistent storage.
     * - Uses search index to collect product matches and highlighted results.
     * - Uses query suggestions index to search for completions.
     * - Searches category facet values for category suggestions.
     * - Searches additional indices based on entity-to-index mappings (CMS pages, custom entities).
     * - Applies personalization parameters (userToken) if provided.
     * - Returns completions, category suggestions, matched items, and matches grouped by field.
     *
     * @api
     *
     * @return \Generated\Shared\Transfer\SuggestionsSearchResponseTransfer
     */
    public function searchSuggestions(SearchRequestTransfer $searchRequestTransfer): SuggestionsSearchResponseTransfer;
}
