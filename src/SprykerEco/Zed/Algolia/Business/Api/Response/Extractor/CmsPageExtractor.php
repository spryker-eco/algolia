<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Algolia\Business\Api\Response\Extractor;

use Generated\Shared\Transfer\AlgoliaSearchResponseTransfer;
use Generated\Shared\Transfer\SearchRequestTransfer;
use SprykerEco\Shared\Algolia\Enum\AlgoliaCmsPageObjectEnum;
use SprykerEco\Shared\Algolia\Enum\AlgoliaEntityNameEnum;

class CmsPageExtractor implements SearchResponseExtractorInterface
{
    /**
     * @var string
     */
    protected const FIELD_SNIPPET_RESULT = '_snippetResult';

    /**
     * @var string
     */
    protected const FIELD_HIGHLIGHT_RESULT = '_highlightResult';

    /**
     * @return string
     */
    public function getName(): string
    {
        return AlgoliaEntityNameEnum::CMS_PAGE->value;
    }

    /**
     * @param \Generated\Shared\Transfer\SearchRequestTransfer $searchRequestTransfer
     *
     * @return bool
     */
    public function isApplicable(SearchRequestTransfer $searchRequestTransfer): bool
    {
        return $searchRequestTransfer->getSourceIdentifier() === AlgoliaEntityNameEnum::CMS_PAGE->value;
    }

    /**
     * @param \Generated\Shared\Transfer\AlgoliaSearchResponseTransfer $algoliaSearchResponseTransfer
     *
     * @return array<int, array<string, mixed>>
     */
    public function extract(AlgoliaSearchResponseTransfer $algoliaSearchResponseTransfer): array
    {
        $simpleHits = $this->simplifyHits($this->getHits($algoliaSearchResponseTransfer->getSearchResults()));

        return $this->highlightAttributes($simpleHits);
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

    /**
     * Simplifies the hits array by removing unnecessary fields and reducing the response size.
     *
     * This method processes the hits from Algolia search results, excluding fields like
     * `_snippetResult` and `_highlightResult`. It extracts only the essential data, such as
     * the `content` field, to provide a smaller and more efficient response.
     *
     * @param array<int, array<string, mixed>> $hits
     *
     * @return array<int, array<string, mixed>>
     */
    protected function simplifyHits(array $hits): array
    {
        $simpleHits = [];
        foreach ($hits as $hit) {
            $simpleHit = [];
            foreach ($hit as $name => $value) {
                if ($name === static::FIELD_SNIPPET_RESULT) {
                    continue;
                }
                $simpleHit[$name] = $value;
            }
            $simpleHit[AlgoliaCmsPageObjectEnum::CONTENT->value] = $hit[static::FIELD_SNIPPET_RESULT][AlgoliaCmsPageObjectEnum::CONTENT->value]['value'] ?? '';
            // Remove the "store" field as it is not needed in the simplified response
            unset($simpleHit[AlgoliaCmsPageObjectEnum::STORE->value]);

            $simpleHits[] = $simpleHit;
        }

        return $simpleHits;
    }

    /**
     * @param array<int, array<string, mixed>> $simpleHits
     *
     * @return array<int, array<string, mixed>>
     */
    protected function highlightAttributes(array $simpleHits): array
    {
        foreach ($simpleHits as &$simpleHit) {
            if (empty($simpleHit[static::FIELD_HIGHLIGHT_RESULT])) {
                continue;
            }

            foreach ($simpleHit[static::FIELD_HIGHLIGHT_RESULT] as $key => $value) {
                $simpleHit[$key] = $value['value'] ?? '';
            }
            unset($simpleHit[static::FIELD_HIGHLIGHT_RESULT]);
        }

        return $simpleHits;
    }
}
