<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business;

use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use SprykerEco\Zed\Algolia\Business\Api\Creator\SearchClientCreator;
use SprykerEco\Zed\Algolia\Business\Api\Creator\SearchClientCreatorInterface;
use SprykerEco\Zed\Algolia\Business\Api\Creator\SearchIndexClientCreator;
use SprykerEco\Zed\Algolia\Business\Api\Creator\SearchIndexClientCreatorInterface;
use SprykerEco\Zed\Algolia\Business\Api\IndexConfigurator\IndexConfigurator;
use SprykerEco\Zed\Algolia\Business\Api\IndexReader\IndexMapper;
use SprykerEco\Zed\Algolia\Business\Api\IndexReader\IndexReader;
use SprykerEco\Zed\Algolia\Business\Api\IndexReader\IndexReaderInterface;
use SprykerEco\Zed\Algolia\Business\Api\Response\Builder\SearchResponseBuilder;
use SprykerEco\Zed\Algolia\Business\Api\Response\Builder\SearchResponseBuilderInterface;
use SprykerEco\Zed\Algolia\Business\Api\Response\Builder\SuggestionsSearchResponseBuilder;
use SprykerEco\Zed\Algolia\Business\Api\Response\Builder\SuggestionsSearchResponseBuilderInterface;
use SprykerEco\Zed\Algolia\Business\Api\Response\Extractor\CategoryExtractor;
use SprykerEco\Zed\Algolia\Business\Api\Response\Extractor\CmsPageExtractor;
use SprykerEco\Zed\Algolia\Business\Api\Response\Extractor\CompletionsExtractor;
use SprykerEco\Zed\Algolia\Business\Api\Response\Extractor\FacetsExtractor;
use SprykerEco\Zed\Algolia\Business\Api\Response\Extractor\FacetsExtractorInterface;
use SprykerEco\Zed\Algolia\Business\Api\Response\Extractor\PaginationExtractor;
use SprykerEco\Zed\Algolia\Business\Api\Response\Extractor\PaginationExtractorInterface;
use SprykerEco\Zed\Algolia\Business\Api\Response\Extractor\ProductsExtractor;
use SprykerEco\Zed\Algolia\Business\Api\Response\Extractor\SearchResponseExtractorInterface;
use SprykerEco\Zed\Algolia\Business\Api\Response\Extractor\SuggestionsCmsPageExtractor;
use SprykerEco\Zed\Algolia\Business\Api\Response\Extractor\SuggestionsProductsExtractor;
use SprykerEco\Zed\Algolia\Business\Api\SearchParameters\Expander\CmsPageSearchParametersExpander;
use SprykerEco\Zed\Algolia\Business\Api\SearchParameters\Expander\SearchParametersExpanderInterface;
use SprykerEco\Zed\Algolia\Business\Api\SearchParameters\Filter\FilterConverter;
use SprykerEco\Zed\Algolia\Business\Api\SearchParameters\Filter\FilterConverterInterface;
use SprykerEco\Zed\Algolia\Business\Api\SearchParameters\Pagination\PaginationConverter;
use SprykerEco\Zed\Algolia\Business\Api\SearchParameters\Pagination\PaginationConverterInterface;
use SprykerEco\Zed\Algolia\Business\Api\SearchParameters\SearchParametersResolver;
use SprykerEco\Zed\Algolia\Business\Api\SearchParameters\SearchParametersResolverInterface;
use SprykerEco\Zed\Algolia\Business\Creator\ProductCreator;
use SprykerEco\Zed\Algolia\Business\Creator\ProductCreatorInterface;
use SprykerEco\Zed\Algolia\Business\Deleter\CmsPageDeleter;
use SprykerEco\Zed\Algolia\Business\Deleter\CmsPageDeleterInterface;
use SprykerEco\Zed\Algolia\Business\Deleter\ProductDeleter;
use SprykerEco\Zed\Algolia\Business\Deleter\ProductDeleterInterface;
use SprykerEco\Zed\Algolia\Business\Exporter\ProductExporter;
use SprykerEco\Zed\Algolia\Business\Exporter\ProductExporterInterface;
use SprykerEco\Zed\Algolia\Business\Filter\PriceProductDataFilter;
use SprykerEco\Zed\Algolia\Business\Filter\ProductConcreteFilter;
use SprykerEco\Zed\Algolia\Business\Filter\ProductConcreteFilterInterface;
use SprykerEco\Zed\Algolia\Business\Filter\ProductDataFilterApplier;
use SprykerEco\Zed\Algolia\Business\Filter\ProductDataFilterApplierInterface;
use SprykerEco\Zed\Algolia\Business\Handler\SuggestionIndexHandler;
use SprykerEco\Zed\Algolia\Business\Handler\SuggestionIndexHandlerInterface;
use SprykerEco\Zed\Algolia\Business\Indexer\CmsPageIndexer;
use SprykerEco\Zed\Algolia\Business\Indexer\CmsPageIndexerInterface;
use SprykerEco\Zed\Algolia\Business\Indexer\ProductIndexer;
use SprykerEco\Zed\Algolia\Business\Indexer\ProductIndexerInterface;
use SprykerEco\Zed\Algolia\Business\IndexResolver\IndexNameResolver;
use SprykerEco\Zed\Algolia\Business\IndexResolver\SearchIndexResolver;
use SprykerEco\Zed\Algolia\Business\Mapper\CmsPageMapper;
use SprykerEco\Zed\Algolia\Business\Mapper\CmsPageMapperInterface;
use SprykerEco\Zed\Algolia\Business\Mapper\CredentialsMapper;
use SprykerEco\Zed\Algolia\Business\Mapper\CredentialsMapperInterface;
use SprykerEco\Zed\Algolia\Business\Mapper\ProductMapper;
use SprykerEco\Zed\Algolia\Business\Mapper\ProductMapperInterface;
use SprykerEco\Zed\Algolia\Business\Publisher\CmsPagePublisher;
use SprykerEco\Zed\Algolia\Business\Publisher\CmsPagePublisherInterface;
use SprykerEco\Zed\Algolia\Business\Resolver\AlgoliaConfigResolver;
use SprykerEco\Zed\Algolia\Business\Saver\CmsPageSaver;
use SprykerEco\Zed\Algolia\Business\Saver\CmsPageSaverInterface;
use SprykerEco\Zed\Algolia\Business\Saver\ProductSaver;
use SprykerEco\Zed\Algolia\Business\Saver\ProductSaverInterface;
use SprykerEco\Zed\Algolia\Business\Searcher\Searcher;
use SprykerEco\Zed\Algolia\Business\Searcher\SuggestionsSearcher;
use SprykerEco\Zed\Algolia\Business\Updater\ProductUpdater;
use SprykerEco\Zed\Algolia\Business\Updater\ProductUpdaterInterface;
use SprykerEco\Zed\Algolia\Business\Validator\AdminApiKeyValidator;
use SprykerEco\Zed\Algolia\Business\Validator\ApiCredentialsValidator;
use SprykerEco\Zed\Algolia\Business\Validator\ApiKeyValidatorInterface;
use SprykerEco\Zed\Algolia\Business\Validator\EnabledFeaturesValidator;
use SprykerEco\Zed\Algolia\Business\Validator\SearchOnlyApiKeyValidator;
use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

/**
 * @method \SprykerEco\Zed\Algolia\AlgoliaConfig getConfig()
 */
class AlgoliaBusinessFactory extends AbstractBusinessFactory
{
    /**
     * @return \SprykerEco\Zed\Algolia\Business\Exporter\ProductExporterInterface
     */
    public function createProductExporter(): ProductExporterInterface
    {
        return new ProductExporter(
            $this->createProductIndexer(),
            $this->createProductSaver(),
            $this->createProductConcreteFilter(),
            $this->createProductDataFilterApplier(),
            $this->createAlgoliaConfigResolver(),
        );
    }

    /**
     * @return \SprykerEco\Zed\Algolia\Business\Creator\ProductCreatorInterface
     */
    public function createProductCreator(): ProductCreatorInterface
    {
        return new ProductCreator(
            $this->createProductIndexer(),
            $this->createProductSaver(),
            $this->createProductConcreteFilter(),
            $this->createProductDataFilterApplier(),
            $this->createAlgoliaConfigResolver(),
        );
    }

    public function createApiCredentialsValidator(): ApiCredentialsValidator
    {
        return new ApiCredentialsValidator(
            [
                $this->createAdminApiKeyValidator(),
                $this->createSearchOnlyApiKeyValidator(),
                $this->createEnabledFeaturesValidator(),
            ],
        );
    }

    /**
     * @return \SprykerEco\Zed\Algolia\Business\Updater\ProductUpdaterInterface
     */
    public function createProductUpdater(): ProductUpdaterInterface
    {
        return new ProductUpdater(
            $this->createProductIndexer(),
            $this->createProductSaver(),
            $this->createProductDeleter(),
            $this->createProductConcreteFilter(),
            $this->createProductDataFilterApplier(),
            $this->createAlgoliaConfigResolver(),
        );
    }

    /**
     * @return \SprykerEco\Zed\Algolia\Business\Deleter\ProductDeleterInterface
     */
    public function createProductDeleter(): ProductDeleterInterface
    {
        return new ProductDeleter(
            $this->createSearchClientCreator(),
            $this->createSearchIndexClientCreator(),
            $this->createIndexReader(),
            $this->createIndexNameResolver(),
            $this->createAlgoliaConfigResolver(),
        );
    }

    /**
     * @return \SprykerEco\Zed\Algolia\Business\Saver\ProductSaverInterface
     */
    public function createProductSaver(): ProductSaverInterface
    {
        return new ProductSaver(
            $this->createSearchClientCreator(),
            $this->createSearchIndexClientCreator(),
            $this->createProductMapper(),
        );
    }

    /**
     * @return \SprykerEco\Zed\Algolia\Business\Indexer\ProductIndexerInterface
     */
    public function createProductIndexer(): ProductIndexerInterface
    {
        return new ProductIndexer(
            $this->createProductMapper(),
            $this->createIndexNameResolver(),
        );
    }

    /**
     * @return \SprykerEco\Zed\Algolia\Business\Api\IndexReader\IndexReaderInterface
     */
    public function createIndexReader(): IndexReaderInterface
    {
        return new IndexReader($this->createIndexMapper());
    }

    /**
     * @return \SprykerEco\Zed\Algolia\Business\Handler\SuggestionIndexHandlerInterface
     */
    public function createSuggestionIndexHandler(): SuggestionIndexHandlerInterface
    {
        return new SuggestionIndexHandler($this->getConfig());
    }

    /**
     * @return \SprykerEco\Zed\Algolia\Business\IndexResolver\IndexNameResolver
     */
    public function createIndexNameResolver(): IndexNameResolver
    {
        return new IndexNameResolver($this->getConfig());
    }

    /**
     * @return \SprykerEco\Zed\Algolia\Business\Api\Creator\SearchIndexClientCreatorInterface
     */
    public function createSearchIndexClientCreator(): SearchIndexClientCreatorInterface
    {
        return new SearchIndexClientCreator($this->createIndexConfigurator());
    }

    /**
     * @return \SprykerEco\Zed\Algolia\Business\Api\Creator\SearchClientCreatorInterface
     */
    public function createSearchClientCreator(): SearchClientCreatorInterface
    {
        return new SearchClientCreator(
            $this->createAlgoliaConfigResolver(),
            $this->createCredentialsMapper(),
        );
    }

    /**
     * @return \SprykerEco\Zed\Algolia\Business\Mapper\ProductMapperInterface
     */
    public function createProductMapper(): ProductMapperInterface
    {
        return new ProductMapper();
    }

    /**
     * @return \SprykerEco\Zed\Algolia\Business\Api\IndexReader\IndexMapper
     */
    public function createIndexMapper(): IndexMapper
    {
        return new IndexMapper();
    }

    /**
     * @return \SprykerEco\Zed\Algolia\Business\Api\IndexConfigurator\IndexConfigurator
     */
    public function createIndexConfigurator(): IndexConfigurator
    {
        return new IndexConfigurator($this->createSuggestionIndexHandler(), $this->getConfig());
    }

    /**
     * @return \SprykerEco\Zed\Algolia\Business\Filter\ProductConcreteFilterInterface
     */
    public function createProductConcreteFilter(): ProductConcreteFilterInterface
    {
        return new ProductConcreteFilter();
    }

    /**
     * @return \SprykerEco\Zed\Algolia\Business\Resolver\AlgoliaConfigResolver
     */
    public function createAlgoliaConfigResolver(): AlgoliaConfigResolver
    {
        return new AlgoliaConfigResolver();
    }

    /**
     * @return \SprykerEco\Zed\Algolia\Business\Mapper\CredentialsMapperInterface
     */
    public function createCredentialsMapper(): CredentialsMapperInterface
    {
        return new CredentialsMapper();
    }

    /**
     * @return \SprykerEco\Zed\Algolia\Business\Validator\ApiKeyValidatorInterface
     */
    public function createAdminApiKeyValidator(): ApiKeyValidatorInterface
    {
        return new AdminApiKeyValidator(
            $this->createCredentialsMapper(),
            $this->createSearchClientCreator(),
        );
    }

    /**
     * @return \SprykerEco\Zed\Algolia\Business\Validator\ApiKeyValidatorInterface
     */
    public function createSearchOnlyApiKeyValidator(): ApiKeyValidatorInterface
    {
        return new SearchOnlyApiKeyValidator(
            $this->createCredentialsMapper(),
            $this->createSearchClientCreator(),
        );
    }

    public function createEnabledFeaturesValidator(): ApiKeyValidatorInterface
    {
        return new EnabledFeaturesValidator(
            $this->createCredentialsMapper(),
            $this->createSearchClientCreator(),
        );
    }

    /**
     * @return \SprykerEco\Zed\Algolia\Business\Searcher\Searcher
     */
    public function createSearcher(): Searcher
    {
        return new Searcher(
            $this->createSearchIndexResolver(),
            $this->createSearchParametersResolver(),
            $this->createSearchResponseBuilder(),
            $this->createSearchClientCreator(),
            $this->createAlgoliaConfigResolver(),
        );
    }

    public function createSuggestionsSearcher(): SuggestionsSearcher
    {
        return new SuggestionsSearcher(
            $this->createIndexNameResolver(),
            $this->createSearchClientCreator(),
            $this->createSuggestionsSearchResponseBuilder(),
            $this->getConfig(),
            $this->createAlgoliaConfigResolver(),
        );
    }

    public function createSearchIndexResolver(): SearchIndexResolver
    {
        return new SearchIndexResolver(
            $this->createIndexNameResolver(),
            $this->createSearchClientCreator(),
            $this->createSearchIndexClientCreator(),
            $this->createAlgoliaConfigResolver(),
        );
    }

    /**
     * @return \SprykerEco\Zed\Algolia\Business\Api\SearchParameters\SearchParametersResolverInterface
     */
    public function createSearchParametersResolver(): SearchParametersResolverInterface
    {
        return new SearchParametersResolver(
            $this->createFilterConverter(),
            $this->createPaginationConverter(),
            [
                $this->createCmsPageSearchParametersExpander(),
            ],
        );
    }

    /**
     * @return \SprykerEco\Zed\Algolia\Business\Api\SearchParameters\Filter\FilterConverterInterface
     */
    public function createFilterConverter(): FilterConverterInterface
    {
        return new FilterConverter(
            $this->getConfig(),
            $this->createAlgoliaConfigResolver(),
            $this->createSearchIndexResolver(),
            $this->createCache(),
        );
    }

    /**
     * @return \Symfony\Component\Cache\Adapter\FilesystemAdapter
     */
    public function createCache(): AbstractAdapter
    {
        return new FilesystemAdapter();
    }

    /**
     * @return \SprykerEco\Zed\Algolia\Business\Api\SearchParameters\Pagination\PaginationConverterInterface
     */
    public function createPaginationConverter(): PaginationConverterInterface
    {
        return new PaginationConverter();
    }

    /**
     * @return \SprykerEco\Zed\Algolia\Business\Api\Response\Builder\SearchResponseBuilderInterface
     */
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

    /**
     * @return \SprykerEco\Zed\Algolia\Business\Api\Response\Builder\SuggestionsSearchResponseBuilderInterface
     */
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

    /**
     * @return \SprykerEco\Zed\Algolia\Business\Api\Response\Extractor\SearchResponseExtractorInterface
     */
    public function createProductsExtractor(): SearchResponseExtractorInterface
    {
        return new ProductsExtractor();
    }

    /**
     * @return \SprykerEco\Zed\Algolia\Business\Api\Response\Extractor\SearchResponseExtractorInterface
     */
    public function createSuggestionProductsExtractor(): SearchResponseExtractorInterface
    {
        return new SuggestionsProductsExtractor($this->getConfig());
    }

    /**
     * @return \SprykerEco\Zed\Algolia\Business\Api\Response\Extractor\SearchResponseExtractorInterface
     */
    public function createSuggestionsCmsPageExtractor(): SearchResponseExtractorInterface
    {
        return new SuggestionsCmsPageExtractor();
    }

    /**
     * @return \SprykerEco\Zed\Algolia\Business\Api\Response\Extractor\PaginationExtractorInterface
     */
    public function createPaginationExtractor(): PaginationExtractorInterface
    {
        return new PaginationExtractor();
    }

    /**
     * @return \SprykerEco\Zed\Algolia\Business\Api\Response\Extractor\SearchResponseExtractorInterface
     */
    public function createCompletionsExtractor(): SearchResponseExtractorInterface
    {
        return new CompletionsExtractor();
    }

    /**
     * @return \SprykerEco\Zed\Algolia\Business\Api\Response\Extractor\SearchResponseExtractorInterface
     */
    public function createCategoryExtractor(): SearchResponseExtractorInterface
    {
        return new CategoryExtractor();
    }

    /**
     * @return \SprykerEco\Zed\Algolia\Business\Api\Response\Extractor\FacetsExtractorInterface
     */
    public function createFacetsExtractor(): FacetsExtractorInterface
    {
        return new FacetsExtractor($this->getConfig(), $this->createAlgoliaConfigResolver());
    }

    /**
     * @return \SprykerEco\Zed\Algolia\Business\Filter\ProductDataFilterApplierInterface
     */
    public function createProductDataFilterApplier(): ProductDataFilterApplierInterface
    {
        return new ProductDataFilterApplier(
            [
                new PriceProductDataFilter(),
            ],
        );
    }

    /**
     * @return \SprykerEco\Zed\Algolia\Business\Mapper\CmsPageMapperInterface
     */
    public function createCmsPageMapper(): CmsPageMapperInterface
    {
        return new CmsPageMapper();
    }

    /**
     * @return \SprykerEco\Zed\Algolia\Business\Indexer\CmsPageIndexerInterface
     */
    public function createCmsPageIndexer(): CmsPageIndexerInterface
    {
        return new CmsPageIndexer(
            $this->createCmsPageMapper(),
            $this->createIndexNameResolver(),
        );
    }

    /**
     * @return \SprykerEco\Zed\Algolia\Business\Saver\CmsPageSaverInterface
     */
    public function createCmsPageSaver(): CmsPageSaverInterface
    {
        return new CmsPageSaver(
            $this->createSearchClientCreator(),
            $this->createSearchIndexClientCreator(),
            $this->createIndexConfigurator(),
            $this->getConfig(),
        );
    }

    /**
     * @return \SprykerEco\Zed\Algolia\Business\Deleter\CmsPageDeleterInterface
     */
    public function createCmsPageDeleter(): CmsPageDeleterInterface
    {
        return new CmsPageDeleter(
            $this->createSearchClientCreator(),
            $this->createSearchIndexClientCreator(),
            $this->createIndexReader(),
            $this->createIndexNameResolver(),
            $this->createAlgoliaConfigResolver(),
        );
    }

    /**
     * @return \SprykerEco\Zed\Algolia\Business\Publisher\CmsPagePublisherInterface
     */
    public function createCmsPagePublisher(): CmsPagePublisherInterface
    {
        return new CmsPagePublisher(
            $this->createCmsPageIndexer(),
            $this->createCmsPageSaver(),
            $this->createAlgoliaConfigResolver(),
        );
    }

    /**
     * @return \SprykerEco\Zed\Algolia\Business\Api\Response\Extractor\SearchResponseExtractorInterface
     */
    public function createCmsPageExtractor(): SearchResponseExtractorInterface
    {
        return new CmsPageExtractor();
    }

    /**
     * @return \SprykerEco\Zed\Algolia\Business\Api\SearchParameters\Expander\SearchParametersExpanderInterface
     */
    public function createCmsPageSearchParametersExpander(): SearchParametersExpanderInterface
    {
        return new CmsPageSearchParametersExpander($this->getConfig());
    }
}
