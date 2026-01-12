<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Api\Response\Extractor;

use Generated\Shared\Transfer\AlgoliaSearchResponseTransfer;
use Generated\Shared\Transfer\SearchRequestTransfer;

abstract class AbstractFeatureExtractor implements SearchResponseExtractorInterface
{
    public function getName(): string
    {
        return '';
    }

    public function isApplicable(SearchRequestTransfer $searchRequestTransfer): bool
    {
        return true;
    }

    /**
     * @return array
     */
    abstract public function extract(AlgoliaSearchResponseTransfer $algoliaSearchResponseTransfer): array;

    /**
     * Specific case for Query Categorization extension to extract prediction category.
     *
     * @return array<string>
     */
    protected function getCategoriesFromQueryCategorization(AlgoliaSearchResponseTransfer $algoliaSearchResponseTransfer, bool $skipFirstLevel = false): array
    {
        $searchResult = $algoliaSearchResponseTransfer->getSearchResults();
        $categories = [];

        if (isset($searchResult['suggestions']['extensions']['queryCategorization']['categories'])) {
            foreach ((array)$searchResult['suggestions']['extensions']['queryCategorization']['categories'] as $category) {
                $hierarchyCategoryPath = (array)($category['hierarchyPath'] ?? []);
                if (!$hierarchyCategoryPath) {
                    continue;
                }
                $category = end($hierarchyCategoryPath);
                if ($skipFirstLevel && $category['depth'] === 0) {
                    continue;
                }
                $categoryHierarchyName = $category['facetValue'];
                preg_match('/(?P<category>[^>]+)$/', $categoryHierarchyName, $matches);
                if (isset($matches['category'])) {
                    $categories[] = trim($matches['category']);
                }
            }
        }

        return $categories;
    }
}
