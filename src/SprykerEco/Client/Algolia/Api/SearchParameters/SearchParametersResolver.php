<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Client\Algolia\Api\SearchParameters;

use Generated\Shared\Transfer\AlgoliaConfigTransfer;
use Generated\Shared\Transfer\AlgoliaSearchParametersTransfer;
use Generated\Shared\Transfer\SearchRequestTransfer;
use SprykerEco\Client\Algolia\Api\SearchParameters\Expander\SearchParametersExpanderInterface;
use SprykerEco\Client\Algolia\Api\SearchParameters\Filter\FilterConverterInterface;
use SprykerEco\Client\Algolia\Api\SearchParameters\Pagination\PaginationConverterInterface;

class SearchParametersResolver implements SearchParametersResolverInterface
{
    protected FilterConverterInterface $filterConverter;

    protected PaginationConverterInterface $paginationConverter;

    /**
     * @var array<\SprykerEco\Client\Algolia\Api\SearchParameters\Expander\SearchParametersExpanderInterface>
     */
    protected array $searchParametersExpanders;

    /**
     * @param array<\SprykerEco\Client\Algolia\Api\SearchParameters\Expander\SearchParametersExpanderInterface> $searchParametersExpander
     */
    public function __construct(
        FilterConverterInterface $filterConverter,
        PaginationConverterInterface $paginationConverter,
        array $searchParametersExpander
    ) {
        $this->filterConverter = $filterConverter;
        $this->paginationConverter = $paginationConverter;
        $this->searchParametersExpanders = $searchParametersExpander;
    }

    public function getSearchParameters(
        SearchRequestTransfer $searchRequestTransfer,
        AlgoliaConfigTransfer $algoliaConfigTransfer
    ): AlgoliaSearchParametersTransfer {
        $searchParametersExpander = $this->getSearchParametersExpanders($searchRequestTransfer->getSourceIdentifier());
        $filters = $this->filterConverter->convertFacetCollectionTransferToAlgoliaFiltersString($searchRequestTransfer);

        if ($searchParametersExpander) {
            $filters = $searchParametersExpander->expandFilters($filters, $searchRequestTransfer);
        }

        $pagination = $this->paginationConverter->convertPaginationTransferToAlgoliaPaginationArray($searchRequestTransfer->getPagination());

        $additionalParameters = [];

        if ($searchParametersExpander) {
            $additionalParameters = $searchParametersExpander->expandSourceIdentifierParameters($additionalParameters, $searchRequestTransfer);
        }

        if (
            $searchRequestTransfer->getUserToken() && (
                $algoliaConfigTransfer->getIsPersonalizationEnabled() || in_array('personalization', $algoliaConfigTransfer->getEnabledFeatures())
            )
        ) {
            $additionalParameters['enablePersonalization'] = true;
            $additionalParameters['userToken'] = $searchRequestTransfer->getUserTokenOrFail();
        }

        $requestOptions = [];

        if ($searchRequestTransfer->getUserIp()) {
            $requestOptions['headers']['X-Forwarded-For'] = $searchRequestTransfer->getUserIpOrFail();
        }

        $searchParams = [
            'filters' => $filters,
            'facets' => ['*'], // this line is necessary to force Algolia to return all facets in search request together with their aggregation statistic
            'clickAnalytics' => true, // https://www.algolia.com/doc/api-reference/api-parameters/clickAnalytics/
            ...$pagination,
            ...$additionalParameters,
        ];

        return (new AlgoliaSearchParametersTransfer())
            ->setSearchParams($searchParams)
            ->setRequestOptions($requestOptions);
    }

    protected function getSearchParametersExpanders(string $sourceIdentifier): ?SearchParametersExpanderInterface
    {
        foreach ($this->searchParametersExpanders as $searchParametersExpander) {
            if ($searchParametersExpander->isApplicable($sourceIdentifier)) {
                return $searchParametersExpander;
            }
        }

        return null;
    }
}
