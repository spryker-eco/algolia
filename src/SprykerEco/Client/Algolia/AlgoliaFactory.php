<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Algolia;

use Spryker\Client\Customer\CustomerClientInterface;
use Spryker\Client\Kernel\AbstractFactory;
use Spryker\Client\Locale\LocaleClientInterface;
use Spryker\Client\Store\StoreClientInterface;
use SprykerEco\Client\Algolia\Api\Creator\SearchClientCreator;
use SprykerEco\Client\Algolia\Api\Creator\SearchClientCreatorInterface;
use SprykerEco\Client\Algolia\Api\Creator\SearchIndexClientCreator;
use SprykerEco\Client\Algolia\Api\Creator\SearchIndexClientCreatorInterface;
use SprykerEco\Client\Algolia\Api\Response\Builder\SearchResponseBuilder;
use SprykerEco\Client\Algolia\Api\Response\Builder\SearchResponseBuilderInterface;
use SprykerEco\Client\Algolia\Api\Response\Builder\SuggestionsSearchResponseBuilder;
use SprykerEco\Client\Algolia\Api\Response\Builder\SuggestionsSearchResponseBuilderInterface;
use SprykerEco\Client\Algolia\Api\Response\Extractor\CategoryExtractor;
use SprykerEco\Client\Algolia\Api\Response\Extractor\CmsPageExtractor;
use SprykerEco\Client\Algolia\Api\Response\Extractor\CompletionsExtractor;
use SprykerEco\Client\Algolia\Api\Response\Extractor\FacetsExtractor;
use SprykerEco\Client\Algolia\Api\Response\Extractor\FacetsExtractorInterface;
use SprykerEco\Client\Algolia\Api\Response\Extractor\PaginationExtractor;
use SprykerEco\Client\Algolia\Api\Response\Extractor\PaginationExtractorInterface;
use SprykerEco\Client\Algolia\Api\Response\Extractor\ProductsExtractor;
use SprykerEco\Client\Algolia\Api\Response\Extractor\SearchResponseExtractorInterface;
use SprykerEco\Client\Algolia\Api\Response\Extractor\SuggestionsCmsPageExtractor;
use SprykerEco\Client\Algolia\Api\Response\Extractor\SuggestionsProductsExtractor;
use SprykerEco\Client\Algolia\Api\SearchParameters\Expander\CmsPageSearchParametersExpander;
use SprykerEco\Client\Algolia\Api\SearchParameters\Expander\SearchParametersExpanderInterface;
use SprykerEco\Client\Algolia\Api\SearchParameters\Filter\FilterConverter;
use SprykerEco\Client\Algolia\Api\SearchParameters\Filter\FilterConverterInterface;
use SprykerEco\Client\Algolia\Api\SearchParameters\Pagination\PaginationConverter;
use SprykerEco\Client\Algolia\Api\SearchParameters\Pagination\PaginationConverterInterface;
use SprykerEco\Client\Algolia\Api\SearchParameters\SearchParametersResolver;
use SprykerEco\Client\Algolia\Api\SearchParameters\SearchParametersResolverInterface;
use SprykerEco\Client\Algolia\Formatter\SearchRequestFormatter;
use SprykerEco\Client\Algolia\Formatter\SearchRequestFormatterInterface;
use SprykerEco\Client\Algolia\Formatter\SearchResponseFormatter;
use SprykerEco\Client\Algolia\Formatter\SearchResponseFormatterInterface;
use SprykerEco\Client\Algolia\IndexResolver\IndexNameResolver;
use SprykerEco\Client\Algolia\IndexResolver\IndexNameResolverInterface;
use SprykerEco\Client\Algolia\IndexResolver\SearchIndexResolver;
use SprykerEco\Client\Algolia\IndexResolver\SearchIndexResolverInterface;
use SprykerEco\Client\Algolia\Resolver\AlgoliaConfigResolver;
use SprykerEco\Client\Algolia\Resolver\AlgoliaConfigResolverInterface;
use SprykerEco\Client\Algolia\Searcher\Searcher;
use SprykerEco\Client\Algolia\Searcher\SearcherInterface;
use SprykerEco\Client\Algolia\Searcher\SuggestionsSearcher;
use SprykerEco\Client\Algolia\Searcher\SuggestionsSearcherInterface;
use SprykerEco\Client\Algolia\SprykerSearch\Query\QueryApplicabilityChecker;
use SprykerEco\Client\Algolia\SprykerSearch\Query\QueryApplicabilityCheckerInterface;
use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

/**
 * @method \SprykerEco\Client\Algolia\AlgoliaConfig getConfig()
 */
class AlgoliaFactory extends AbstractFactory
{
    public function getStoreClient(): StoreClientInterface
    {
        return $this->getProvidedDependency(AlgoliaDependencyProvider::CLIENT_STORE);
    }

    public function getLocaleClient(): LocaleClientInterface
    {
        return $this->getProvidedDependency(AlgoliaDependencyProvider::CLIENT_LOCALE);
    }

    public function getCustomerClient(): CustomerClientInterface
    {
        return $this->getProvidedDependency(AlgoliaDependencyProvider::CLIENT_CUSTOMER);
    }

    public function createSearcher(): SearcherInterface
    {
        return new Searcher(
            $this->createSearchIndexResolver(),
            $this->createSearchParametersResolver(),
            $this->createSearchResponseBuilder(),
            $this->createSearchClientCreator(),
            $this->createAlgoliaConfigResolver(),
        );
    }

    public function createSuggestionsSearcher(): SuggestionsSearcherInterface
    {
        return new SuggestionsSearcher(
            $this->createIndexNameResolver(),
            $this->createSearchClientCreator(),
            $this->createSuggestionsSearchResponseBuilder(),
            $this->getConfig(),
            $this->createAlgoliaConfigResolver(),
        );
    }

    public function createSearchClientCreator(): SearchClientCreatorInterface
    {
        return new SearchClientCreator(
            $this->createAlgoliaConfigResolver(),
        );
    }

    public function createAlgoliaConfigResolver(): AlgoliaConfigResolverInterface
    {
        return new AlgoliaConfigResolver($this->getConfig());
    }

    public function createSearchIndexResolver(): SearchIndexResolverInterface
    {
        return new SearchIndexResolver(
            $this->createIndexNameResolver(),
            $this->createSearchClientCreator(),
            $this->createSearchIndexClientCreator(),
            $this->createAlgoliaConfigResolver(),
        );
    }

    public function createIndexNameResolver(): IndexNameResolverInterface
    {
        return new IndexNameResolver($this->getConfig());
    }

    public function createSearchParametersResolver(): SearchParametersResolverInterface
    {
        return new SearchParametersResolver(
            $this->createFilterConverter(),
            $this->createPaginationConverter(),
            [$this->createCmsPageSearchParametersExpander()],
        );
    }

    public function createFilterConverter(): FilterConverterInterface
    {
        return new FilterConverter(
            $this->getConfig(),
            $this->createAlgoliaConfigResolver(),
            $this->createSearchIndexResolver(),
            $this->createCache(),
        );
    }

    public function createCache(): AbstractAdapter
    {
        return new FilesystemAdapter();
    }

    public function createPaginationConverter(): PaginationConverterInterface
    {
        return new PaginationConverter();
    }

    public function createSearchResponseBuilder(): SearchResponseBuilderInterface
    {
        return new SearchResponseBuilder(
            [
                $this->createProductsExtractor(),
                $this->createCmsPageExtractor(),
            ],
            $this->createPaginationExtractor(),
            $this->createFacetsExtractor(),
        );
    }

    public function createSuggestionsSearchResponseBuilder(): SuggestionsSearchResponseBuilderInterface
    {
        return new SuggestionsSearchResponseBuilder(
            $this->createCompletionsExtractor(),
            [
                $this->createSuggestionProductsExtractor(),
                $this->createSuggestionsCmsPageExtractor(),
            ],
            $this->createCategoryExtractor(),
        );
    }

    public function createProductsExtractor(): SearchResponseExtractorInterface
    {
        return new ProductsExtractor();
    }

    public function createCmsPageExtractor(): SearchResponseExtractorInterface
    {
        return new CmsPageExtractor();
    }

    public function createPaginationExtractor(): PaginationExtractorInterface
    {
        return new PaginationExtractor();
    }

    public function createFacetsExtractor(): FacetsExtractorInterface
    {
        return new FacetsExtractor($this->getConfig());
    }

    public function createCompletionsExtractor(): SearchResponseExtractorInterface
    {
        return new CompletionsExtractor();
    }

    public function createCategoryExtractor(): SearchResponseExtractorInterface
    {
        return new CategoryExtractor();
    }

    public function createSuggestionProductsExtractor(): SearchResponseExtractorInterface
    {
        return new SuggestionsProductsExtractor($this->getConfig());
    }

    public function createSuggestionsCmsPageExtractor(): SearchResponseExtractorInterface
    {
        return new SuggestionsCmsPageExtractor();
    }

    public function createSearchIndexClientCreator(): SearchIndexClientCreatorInterface
    {
        return new SearchIndexClientCreator();
    }

    public function createCmsPageSearchParametersExpander(): SearchParametersExpanderInterface
    {
        return new CmsPageSearchParametersExpander($this->getConfig());
    }

    public function createQueryApplicabilityChecker(): QueryApplicabilityCheckerInterface
    {
        return new QueryApplicabilityChecker(
            $this->getConfig(),
        );
    }

    public function createSearchRequestFormatter(): SearchRequestFormatterInterface
    {
        return new SearchRequestFormatter();
    }

    public function createSearchResponseFormatter(): SearchResponseFormatterInterface
    {
        return new SearchResponseFormatter();
    }
}
