<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Algolia\Plugin\Search;

use Generated\Shared\Transfer\SearchContextTransfer;
use Generated\Shared\Transfer\SearchDocumentTransfer;
use Spryker\Client\Kernel\AbstractPlugin;
use Spryker\Client\SearchExtension\Dependency\Plugin\QueryInterface;
use Spryker\Client\SearchExtension\Dependency\Plugin\SearchAdapterPluginInterface;
use Spryker\Client\SearchExtension\Dependency\Plugin\SearchTypeIdentifierInterface;
use SprykerEco\Client\Algolia\AlgoliaConfig;

/**
 * @method \SprykerEco\Client\Algolia\AlgoliaClientInterface getClient()
 * @method \SprykerEco\Client\Algolia\AlgoliaFactory getFactory()
 */
class AlgoliaSearchAdapterPlugin extends AbstractPlugin implements SearchAdapterPluginInterface
{
    protected const string NAME = 'algolia';

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function isApplicable(SearchContextTransfer $searchContextTransfer): bool
    {
        return $this->getFactory()->createQueryApplicabilityChecker()->isQueryApplicable($searchContextTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getName(): string
    {
        return static::NAME;
    }

    /**
     * {@inheritDoc}
     * - Performs search request using Algolia client.
     *
     * @api
     *
     * @param array<\Spryker\Client\SearchExtension\Dependency\Plugin\ResultFormatterPluginInterface> $resultFormatters
     * @param array<string, mixed> $requestParameters
     *
     * @return \Elastica\ResultSet|array<string, mixed>
     */
    public function search(QueryInterface $searchQuery, array $resultFormatters = [], array $requestParameters = [])
    {
        $searchResponseFormatter = $this->getFactory()->createSearchResponseFormatter();
        $searchRequestFormatter = $this->getFactory()->createSearchRequestFormatter();

        if (
            $searchQuery instanceof SearchTypeIdentifierInterface &&
            in_array($searchQuery->getSearchType(), [AlgoliaConfig::TYPE_SUGGESTION_SEARCH_HTTP, AlgoliaConfig::TYPE_PRODUCT_CONCRETE_SEARCH_HTTP])
        ) {
            $searchRequest = $searchRequestFormatter->formatSuggestionRequest($searchQuery, $requestParameters);
            $suggestionsSearchResponseTransfer = $this->getClient()->searchSuggestions($searchRequest);

            return $searchResponseFormatter->formatSuggestion($suggestionsSearchResponseTransfer, $resultFormatters, $requestParameters);
        }

        $searchRequest = $searchRequestFormatter->formatRequest($searchQuery, $requestParameters);
        $searchResponseTransfer = $this->getClient()->search($searchRequest);

        return $searchResponseFormatter->format($searchResponseTransfer, $resultFormatters, $requestParameters);
    }

    /**
     * {@inheritDoc}
     * - Currently not supported by the plugin.
     *
     * @api
     */
    public function readDocument(SearchDocumentTransfer $searchDocumentTransfer): SearchDocumentTransfer
    {
        return new SearchDocumentTransfer();
    }

    /**
     * {@inheritDoc}
     * - Currently not supported by the plugin.
     *
     * @api
     */
    public function deleteDocument(SearchDocumentTransfer $searchDocumentTransfer): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     * - Currently not supported by the plugin.
     *
     * @api
     *
     * @param array<\Generated\Shared\Transfer\SearchDocumentTransfer> $searchDocumentTransfers
     */
    public function deleteDocuments(array $searchDocumentTransfers): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     * - Currently not supported by the plugin.
     *
     * @api
     */
    public function writeDocument(SearchDocumentTransfer $searchDocumentTransfer): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     * - Currently not supported by the plugin.
     *
     * @api
     *
     * @param array<\Generated\Shared\Transfer\SearchDocumentTransfer> $searchDocumentTransfers
     */
    public function writeDocuments(array $searchDocumentTransfers): bool
    {
        return true;
    }
}
