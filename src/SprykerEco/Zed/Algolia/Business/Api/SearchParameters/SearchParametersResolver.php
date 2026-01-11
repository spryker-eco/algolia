<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Algolia\Business\Api\SearchParameters;

use Generated\Shared\Transfer\AlgoliaConfigTransfer;
use Generated\Shared\Transfer\SearchRequestTransfer;
use SprykerEco\Zed\Algolia\AlgoliaConfig;
use SprykerEco\Zed\Algolia\Business\Api\SearchParameters\Expander\SearchParametersExpanderInterface;
use SprykerEco\Zed\Algolia\Business\Api\SearchParameters\Filter\FilterConverterInterface;
use SprykerEco\Zed\Algolia\Business\Api\SearchParameters\Pagination\PaginationConverterInterface;

class SearchParametersResolver implements SearchParametersResolverInterface
{
    /**
     * @var \SprykerEco\Zed\Algolia\Business\Api\SearchParameters\Filter\FilterConverterInterface
     */
    protected FilterConverterInterface $filterConverter;

    /**
     * @var \SprykerEco\Zed\Algolia\Business\Api\SearchParameters\Pagination\PaginationConverterInterface
     */
    protected PaginationConverterInterface $paginationConverter;

    /**
     * @var array<\SprykerEco\Zed\Algolia\Business\Api\SearchParameters\Expander\SearchParametersExpanderInterface>
     */
    protected array $searchParametersExpanders;

    /**
     * @param \SprykerEco\Zed\Algolia\Business\Api\SearchParameters\Filter\FilterConverterInterface $filterConverter
     * @param \SprykerEco\Zed\Algolia\Business\Api\SearchParameters\Pagination\PaginationConverterInterface $paginationConverter
     * @param array<\SprykerEco\Zed\Algolia\Business\Api\SearchParameters\Expander\SearchParametersExpanderInterface> $searchParametersExpander
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

    /**
     * @return array<string, mixed>
     */
    public function getSearchParameters(SearchRequestTransfer $searchRequestTransfer, AlgoliaConfigTransfer $algoliaConfigTransfer): array
    {
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

        if ($searchRequestTransfer->getUserToken() && in_array(AlgoliaConfig::FEATURE_PERSONALIZATION, $algoliaConfigTransfer->getEnabledFeatures())) {
            $additionalParameters['enablePersonalization'] = true;
            $additionalParameters['userToken'] = $searchRequestTransfer->getUserTokenOrFail();
        }

        if ($searchRequestTransfer->getUserIp()) {
            // it's used by Algolia instead when userToken is empty
            $additionalParameters['X-Forwarded-For'] = $searchRequestTransfer->getUserIpOrFail();
        }

        return [
            'filters' => $filters,
            'facets' => ['*'], // this line is necessary to force Algolia to return all facets in search request together with their aggregation statistic
            'clickAnalytics' => true, // https://www.algolia.com/doc/api-reference/api-parameters/clickAnalytics/
            ...$pagination,
            ...$additionalParameters,
        ];
    }

    /**
     * @param string $sourceIdentifier
     *
     * @return \SprykerEco\Zed\Algolia\Business\Api\SearchParameters\Expander\SearchParametersExpanderInterface|null
     */
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
