<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Algolia\Formatter;

use Generated\Shared\Transfer\FacetCollectionTransfer;
use Generated\Shared\Transfer\FacetEntryTransfer;
use Generated\Shared\Transfer\FacetParametersTransfer;
use Generated\Shared\Transfer\PaginationEntryTransfer;
use Generated\Shared\Transfer\SearchQueryPaginationTransfer;
use Generated\Shared\Transfer\SearchQueryRangeFacetFilterTransfer;
use Generated\Shared\Transfer\SearchQuerySortingTransfer;
use Generated\Shared\Transfer\SearchQueryTransfer;
use Generated\Shared\Transfer\SearchQueryValueFacetFilterTransfer;
use Generated\Shared\Transfer\SearchRequestTransfer;
use Generated\Shared\Transfer\SortingEntryTransfer;
use Spryker\Client\SearchExtension\Dependency\Plugin\QueryInterface;
use Spryker\Client\SearchExtension\Dependency\Plugin\SearchContextAwareQueryInterface;

class SearchRequestFormatter implements SearchRequestFormatterInterface
{
    /**
     * @var string
     */
    protected const FACET_TYPE_VALUES = 'values';

    /**
     * @var string
     */
    protected const FACET_TYPE_RANGE = 'range';

    /**
     * @param array<string, mixed> $requestParameters
     */
    public function formatRequest(QueryInterface $searchQuery, array $requestParameters): SearchRequestTransfer
    {
        $searchRequestTransfer = $this->prepareAlgoliaSearchRequest($searchQuery);
        $searchQueryTransfer = $searchQuery->getSearchQuery();

        $searchRequestTransfer->setFacets(new FacetCollectionTransfer());
        if ($searchQueryTransfer->getSearchQueryFacetFilters()) {
            $searchRequestTransfer->setFacets($this->mapFacetFilters($searchQueryTransfer));
        }

        if ($searchQueryTransfer->getSort()) {
            $searchRequestTransfer->setSort($this->mapSort($searchQueryTransfer->getSort()));
        }

        return $searchRequestTransfer;
    }

    /**
     * @param array<string, mixed> $requestParameters
     */
    public function formatSuggestionRequest(QueryInterface $searchQuery, array $requestParameters): SearchRequestTransfer
    {
        return $this->prepareAlgoliaSearchRequest($searchQuery);
    }

    protected function prepareAlgoliaSearchRequest(QueryInterface $searchQuery): SearchRequestTransfer
    {
        $searchRequestTransfer = new SearchRequestTransfer();
        if ($searchQuery instanceof SearchContextAwareQueryInterface) {
            $searchRequestTransfer->setSourceIdentifier($searchQuery->getSearchContext()->getSourceIdentifier());
        }

        /** @var \Generated\Shared\Transfer\SearchQueryTransfer $searchQueryTransfer */
        $searchQueryTransfer = $searchQuery->getSearchQuery();

        $searchRequestTransfer
            ->setQuery($searchQueryTransfer->getQueryString())
            ->setLocale($searchQueryTransfer->getLocale())
            ->setUserToken($searchQueryTransfer->getUserToken())
            ->setUserIp($_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? null) // it's used by Algolia instead when userToken is empty
            ->setStoreName($searchQueryTransfer->getStore());

        $searchRequestTransfer->setPagination($this->mapPagination($searchQueryTransfer->getPagination()));

        return $searchRequestTransfer;
    }

    protected function mapFacetFilters(SearchQueryTransfer $searchQueryTransfer): FacetCollectionTransfer
    {
        $facetCollectionTransfer = new FacetCollectionTransfer();

        foreach ($searchQueryTransfer->getSearchQueryFacetFilters() as $facetFilter) {
            if ($facetFilter instanceof SearchQueryValueFacetFilterTransfer) {
                $facetEntryTransfer = $this->mapValueFacetFilter($facetFilter);
                $facetCollectionTransfer->addFacet($facetFilter->getFieldName(), $facetEntryTransfer);
            } elseif ($facetFilter instanceof SearchQueryRangeFacetFilterTransfer) {
                $facetEntryTransfer = $this->mapRangeFacetFilter($facetFilter);
                $facetCollectionTransfer->addFacet($facetFilter->getFieldName(), $facetEntryTransfer);
            }
        }

        return $facetCollectionTransfer;
    }

    protected function mapValueFacetFilter(SearchQueryValueFacetFilterTransfer $valueFacetFilter): FacetEntryTransfer
    {
        $facetParametersTransfer = (new FacetParametersTransfer())
            ->setValues($valueFacetFilter->getValues());

        return (new FacetEntryTransfer())
            ->setType(static::FACET_TYPE_VALUES)
            ->setParameters($facetParametersTransfer);
    }

    protected function mapRangeFacetFilter(SearchQueryRangeFacetFilterTransfer $rangeFacetFilter): FacetEntryTransfer
    {
        $facetParametersTransfer = new FacetParametersTransfer();

        if ($rangeFacetFilter->getFrom() !== null) {
            $facetParametersTransfer->setFrom((int)$rangeFacetFilter->getFrom());
        }

        if ($rangeFacetFilter->getTo() !== null) {
            $facetParametersTransfer->setTo((int)$rangeFacetFilter->getTo());
        }

        return (new FacetEntryTransfer())
            ->setType(static::FACET_TYPE_RANGE)
            ->setParameters($facetParametersTransfer);
    }

    protected function mapSort(SearchQuerySortingTransfer $sortingTransfer): SortingEntryTransfer
    {
        return (new SortingEntryTransfer())
            ->setField($sortingTransfer->getFieldName())
            ->setDirection($sortingTransfer->getSortDirection());
    }

    protected function mapPagination(?SearchQueryPaginationTransfer $paginationTransfer = null): PaginationEntryTransfer
    {
        $page = $paginationTransfer?->getPage() ?? 1;
        $itemsPerPage = $paginationTransfer?->getItemsPerPage() ?? 1;
        $offset = ($page - 1) * $itemsPerPage;

        return (new PaginationEntryTransfer())
            ->setPage($page)
            ->setHitsPerPage($itemsPerPage)
            ->setOffset($offset)
            ->setLength($itemsPerPage);
    }
}
