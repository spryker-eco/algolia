<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Algolia\Api\Response\Extractor;

use Generated\Shared\Transfer\AlgoliaSearchResponseTransfer;

class CategoryExtractor extends AbstractFeatureExtractor
{
    /**
     * @var string
     */
    protected const FIELD_FACET_HITS = 'facetHits';

    /**
     * @var string
     */
    protected const FIELD_CATEGORIES = 'categories';

    /**
     * @var string
     */
    protected const FIELD_VALUE = 'value';

    /**
     * @return array<int, mixed>
     */
    public function extract(AlgoliaSearchResponseTransfer $algoliaSearchResponseTransfer): array
    {
        $searchResult = $algoliaSearchResponseTransfer->getSearchResults();
        $categories = [];

        if (isset($searchResult[static::FIELD_CATEGORIES])) {
            $hits = $this->getHits($searchResult[static::FIELD_CATEGORIES]);

            $categories = array_column($hits, static::FIELD_VALUE);
        }

        $categories = array_merge($categories, $this->getCategoriesFromQueryCategorization($algoliaSearchResponseTransfer));

        return array_unique($categories);
    }

    /**
     * @param array<mixed> $searchResults
     *
     * @return array<int, mixed>
     */
    protected function getHits(array $searchResults): array
    {
        if (isset($searchResults[static::FIELD_FACET_HITS]) && is_array($searchResults[static::FIELD_FACET_HITS])) {
            return $searchResults[static::FIELD_FACET_HITS];
        }

        return [];
    }
}
