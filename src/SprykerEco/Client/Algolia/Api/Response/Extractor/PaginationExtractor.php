<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Algolia\Api\Response\Extractor;

use Generated\Shared\Transfer\AlgoliaSearchResponseTransfer;
use Generated\Shared\Transfer\SearchRequestTransfer;
use Generated\Shared\Transfer\SearchResponsePaginationTransfer;

class PaginationExtractor implements PaginationExtractorInterface
{
    /**
     * @var string
     */
    protected const FIELD_NB_HITS = 'nbHits';

    /**
     * @var string
     */
    protected const FIELD_PAGE = 'page';

    /**
     * @var string
     */
    protected const FIELD_HITS_PER_PAGE = 'hitsPerPage';

    public function extract(
        AlgoliaSearchResponseTransfer $algoliaSearchResponseTransfer,
        SearchRequestTransfer $searchRequestTransfer
    ): SearchResponsePaginationTransfer {
        return (new SearchResponsePaginationTransfer())
            ->setNumFound($this->getNumFound($algoliaSearchResponseTransfer->getSearchResults()))
            ->setCurrentPage($this->getCurrentPage($algoliaSearchResponseTransfer->getSearchResults()))
            ->setCurrentItemsPerPage($this->getCurrentItemsPerPage($algoliaSearchResponseTransfer->getSearchResults(), $searchRequestTransfer));
    }

    /**
     * @param array<mixed> $searchResults
     */
    protected function getNumFound(array $searchResults): int
    {
        return $searchResults[static::FIELD_NB_HITS] ?? 0;
    }

    /**
     * @param array<mixed> $searchResults
     */
    protected function getCurrentPage(array $searchResults): int
    {
        return ($searchResults[static::FIELD_PAGE] ?? 0) + 1;
    }

    /**
     * @param array<string, mixed> $searchResults
     */
    protected function getCurrentItemsPerPage(array $searchResults, SearchRequestTransfer $searchRequestTransfer): int
    {
        $defaultHitsPerPage = 20;
        $paginationEntry = $searchRequestTransfer->getPagination();
        if ($paginationEntry) {
            $defaultHitsPerPage = $paginationEntry->getHitsPerPage() ?? $defaultHitsPerPage;
        }

        return $searchResults[static::FIELD_HITS_PER_PAGE] ?? $defaultHitsPerPage;
    }
}
