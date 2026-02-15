<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Algolia\Plugin\Search;

use Generated\Shared\Transfer\SearchContextTransfer;
use Generated\Shared\Transfer\SearchQueryTransfer;
use Spryker\Client\Kernel\AbstractPlugin;
use Spryker\Client\SearchExtension\Dependency\Plugin\QueryApplicabilityCheckerInterface;
use Spryker\Client\SearchExtension\Dependency\Plugin\QueryInterface;
use Spryker\Client\SearchExtension\Dependency\Plugin\SearchContextAwareQueryInterface;
use Spryker\Client\SearchExtension\Dependency\Plugin\SearchStringSetterInterface;
use Spryker\Client\SearchExtension\Dependency\Plugin\SearchTypeIdentifierInterface;
use Spryker\Shared\SearchHttp\SearchHttpConfig;
use SprykerEco\Client\Algolia\AlgoliaConfig;

/**
 * @method \SprykerEco\Client\Algolia\AlgoliaFactory getFactory()
 */
class AlgoliaSearchQueryPlugin extends AbstractPlugin implements QueryInterface, SearchContextAwareQueryInterface, SearchStringSetterInterface, QueryApplicabilityCheckerInterface, SearchTypeIdentifierInterface
{
    protected SearchQueryTransfer $searchQueryTransfer;

    protected SearchContextTransfer $searchContextTransfer;

    /**
     * @param \Generated\Shared\Transfer\SearchContextTransfer|null $searchContextTransfer
     */
    public function __construct(?SearchContextTransfer $searchContextTransfer = null)
    {
        $this->searchContextTransfer = $searchContextTransfer ?? (new SearchContextTransfer())
            ->setSourceIdentifier(AlgoliaConfig::SOURCE_IDENTIFIER_PRODUCT);

        $this->searchQueryTransfer = (new SearchQueryTransfer())
            ->setLocale($this->getFactory()->getLocaleClient()->getCurrentLocale())
            ->setUserToken($this->getFactory()->getCustomerClient()->getUserIdentifier())
            ->setStore($this->getFactory()->getStoreClient()->getCurrentStore()->getName());
    }

    /**
     * {@inheritDoc}
     * - Returns query object for catalog search.
     *
     * @api
     */
    public function getSearchQuery(): SearchQueryTransfer
    {
        return $this->searchQueryTransfer;
    }

    /**
     * {@inheritDoc}
     * - Defines a context for catalog search.
     *
     * @api
     */
    public function getSearchContext(): SearchContextTransfer
    {
        return $this->searchContextTransfer;
    }

    /**
     * {@inheritDoc}
     * - Sets a context for catalog search.
     *
     * @api
     */
    public function setSearchContext(SearchContextTransfer $searchContextTransfer): void
    {
        $this->searchContextTransfer = $searchContextTransfer;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param string $searchString
     */
    public function setSearchString($searchString): void
    {
        $this->searchQueryTransfer->setQueryString($searchString);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function isApplicable(): bool
    {
        return $this->getFactory()->createQueryApplicabilityChecker()->isQueryApplicable($this->searchContextTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getSearchType(): string
    {
        return SearchHttpConfig::TYPE_SEARCH_HTTP;
    }
}
