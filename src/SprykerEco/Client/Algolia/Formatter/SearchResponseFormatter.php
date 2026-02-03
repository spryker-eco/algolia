<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Algolia\Formatter;

use Generated\Shared\Transfer\SearchHttpResponsePaginationTransfer;
use Generated\Shared\Transfer\SearchHttpResponseTransfer;
use Generated\Shared\Transfer\SearchResponseTransfer;
use Generated\Shared\Transfer\SuggestionsSearchHttpResponseTransfer;
use Generated\Shared\Transfer\SuggestionsSearchResponseTransfer;
use Spryker\Client\SearchExtension\Dependency\Plugin\GroupedResultFormatterPluginInterface;

class SearchResponseFormatter implements SearchResponseFormatterInterface
{
    protected const string DEFAULT_PAGINATION_FORMATTER = 'pagination';

    /**
     * @param array<\Spryker\Client\SearchExtension\Dependency\Plugin\ResultFormatterPluginInterface> $resultFormatters
     * @param array<string, mixed> $requestParameters
     *
     * @return array<string, mixed>
     */
    public function format(
        SearchResponseTransfer $algoliaSearchResponseTransfer,
        array $resultFormatters = [],
        array $requestParameters = []
    ): array {
        $searchHttpResponseTransfer = $this->mapSearchResponseToSearchHttpResponse($algoliaSearchResponseTransfer);

        if ($requestParameters === []) {
            return [static::DEFAULT_PAGINATION_FORMATTER => $algoliaSearchResponseTransfer->getPagination()];
        }

        $formattedResults = [];
        foreach ($resultFormatters as $resultFormatter) {
            $formattedResults[$resultFormatter->getName()] = $resultFormatter->formatResult($searchHttpResponseTransfer, $requestParameters);
        }

        return $formattedResults;
    }

    /**
     * @param array<\Spryker\Client\SearchExtension\Dependency\Plugin\ResultFormatterPluginInterface> $resultFormatters
     * @param array<string, mixed> $requestParameters
     *
     * @return array<string, mixed>
     */
    public function formatSuggestion(
        SuggestionsSearchResponseTransfer $algoliaSuggestionsSearchResponseTransfer,
        array $resultFormatters = [],
        array $requestParameters = []
    ): array {
        $suggestionsSearchHttpResponseTransfer = $this->mapSuggestionsSearchResponseToSuggestionsSearchHttpResponse($algoliaSuggestionsSearchResponseTransfer);

        $formattedResults = [];
        foreach ($resultFormatters as $resultFormatter) {
            $result = $resultFormatter->formatResult($suggestionsSearchHttpResponseTransfer, $requestParameters);
            if ($resultFormatter instanceof GroupedResultFormatterPluginInterface) {
                $formattedResults[$resultFormatter->getGroupName()][$resultFormatter->getName()] = $result;

                continue;
            }
            $formattedResults[$resultFormatter->getName()] = $result;
        }

        return $formattedResults;
    }

    protected function mapSearchResponseToSearchHttpResponse(SearchResponseTransfer $searchResponseTransfer): SearchHttpResponseTransfer
    {
        $searchHttpResponseTransfer = new SearchHttpResponseTransfer();
        $searchHttpResponseTransfer
            ->setItems($searchResponseTransfer->getItems())
            ->setFacets($searchResponseTransfer->getFacets())
            ->setQueryId($searchResponseTransfer->getQueryId());

        if ($searchResponseTransfer->getPagination()) {
            $paginationTransfer = $searchResponseTransfer->getPagination();
            $searchHttpPaginationTransfer = (new SearchHttpResponsePaginationTransfer())
                ->setNumFound($paginationTransfer->getNumFound())
                ->setCurrentPage($paginationTransfer->getCurrentPage())
                ->setCurrentItemsPerPage($paginationTransfer->getCurrentItemsPerPage());

            $searchHttpResponseTransfer->setPagination($searchHttpPaginationTransfer);
        }

        return $searchHttpResponseTransfer;
    }

    protected function mapSuggestionsSearchResponseToSuggestionsSearchHttpResponse(
        SuggestionsSearchResponseTransfer $suggestionsSearchResponseTransfer
    ): SuggestionsSearchHttpResponseTransfer {
        $suggestionsSearchHttpResponseTransfer = new SuggestionsSearchHttpResponseTransfer();
        $suggestionsSearchHttpResponseTransfer
            ->setCompletions($suggestionsSearchResponseTransfer->getCompletions())
            ->setMatches($suggestionsSearchResponseTransfer->getMatches())
            ->setMatchedItems($suggestionsSearchResponseTransfer->getMatchedItems())
            ->setCategories($suggestionsSearchResponseTransfer->getCategories())
            ->setMatchedItemsBySourceIdentifiers($suggestionsSearchResponseTransfer->getMatchedItemsBySourceIdentifiers());

        return $suggestionsSearchHttpResponseTransfer;
    }
}
