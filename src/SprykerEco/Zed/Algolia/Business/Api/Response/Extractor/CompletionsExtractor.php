<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Api\Response\Extractor;

use Generated\Shared\Transfer\AlgoliaSearchResponseTransfer;

class CompletionsExtractor extends AbstractFeatureExtractor
{
    /**
     * @var string
     */
    protected const FIELD_COMPLETIONS = 'completions';

    /**
     * @var string
     */
    protected const FIELD_QUERY = 'query';

    /**
     * @return array<int, mixed>
     */
    public function extract(AlgoliaSearchResponseTransfer $algoliaSearchResponseTransfer): array
    {
        $hits = $this->getHits($algoliaSearchResponseTransfer->getSearchResults()[static::FIELD_COMPLETIONS]);
        $completions = array_column($hits, static::FIELD_QUERY);
        $completions = array_merge($completions, $this->getCategoriesFromQueryCategorization($algoliaSearchResponseTransfer, true));

        return array_unique($completions);
    }

    /**
     * @param array<mixed> $searchResults
     *
     * @return array<int, mixed>
     */
    protected function getHits(array $searchResults): array
    {
        if (isset($searchResults[static::FIELD_HITS]) && is_array($searchResults[static::FIELD_HITS])) {
            return $searchResults[static::FIELD_HITS];
        }

        return [];
    }
}
