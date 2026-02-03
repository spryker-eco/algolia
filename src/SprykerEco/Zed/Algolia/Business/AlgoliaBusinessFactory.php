<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business;

use Spryker\Zed\Cms\Business\CmsFacadeInterface;
use Spryker\Zed\Cms\Persistence\CmsQueryContainerInterface;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use Spryker\Zed\Product\Business\ProductFacadeInterface;
use SprykerEco\Zed\Algolia\AlgoliaDependencyProvider;
use SprykerEco\Zed\Algolia\Business\Api\Creator\SearchClientCreator;
use SprykerEco\Zed\Algolia\Business\Api\Creator\SearchClientCreatorInterface;
use SprykerEco\Zed\Algolia\Business\Api\Creator\SearchIndexClientCreator;
use SprykerEco\Zed\Algolia\Business\Api\Creator\SearchIndexClientCreatorInterface;
use SprykerEco\Zed\Algolia\Business\Api\IndexConfigurator\IndexConfigurator;
use SprykerEco\Zed\Algolia\Business\Api\IndexConfigurator\IndexConfiguratorInterface;
use SprykerEco\Zed\Algolia\Business\Api\IndexReader\IndexMapper;
use SprykerEco\Zed\Algolia\Business\Api\IndexReader\IndexMapperInterface;
use SprykerEco\Zed\Algolia\Business\Api\IndexReader\IndexReader;
use SprykerEco\Zed\Algolia\Business\Api\IndexReader\IndexReaderInterface;
use SprykerEco\Zed\Algolia\Business\Builder\CmsPagePublishedTransferBuilder;
use SprykerEco\Zed\Algolia\Business\Builder\CmsPagePublishedTransferBuilderInterface;
use SprykerEco\Zed\Algolia\Business\Config\AlgoliaConfigResolver;
use SprykerEco\Zed\Algolia\Business\Config\AlgoliaConfigResolverInterface;
use SprykerEco\Zed\Algolia\Business\Deleter\CmsPageDeleter;
use SprykerEco\Zed\Algolia\Business\Deleter\CmsPageDeleterInterface;
use SprykerEco\Zed\Algolia\Business\Deleter\ProductDeleter;
use SprykerEco\Zed\Algolia\Business\Deleter\ProductDeleterInterface;
use SprykerEco\Zed\Algolia\Business\Exporter\AlgoliaEntityExporter;
use SprykerEco\Zed\Algolia\Business\Exporter\AlgoliaEntityExporterInterface;
use SprykerEco\Zed\Algolia\Business\Exporter\CmsPageExporter;
use SprykerEco\Zed\Algolia\Business\Exporter\CmsPageExporterInterface;
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
use SprykerEco\Zed\Algolia\Business\IndexResolver\IndexNameResolverInterface;
use SprykerEco\Zed\Algolia\Business\Mapper\CmsPageMapper;
use SprykerEco\Zed\Algolia\Business\Mapper\CmsPageMapperInterface;
use SprykerEco\Zed\Algolia\Business\Mapper\ProductMapper;
use SprykerEco\Zed\Algolia\Business\Mapper\ProductMapperInterface;
use SprykerEco\Zed\Algolia\Business\Publisher\CmsPagePublisher;
use SprykerEco\Zed\Algolia\Business\Publisher\CmsPagePublisherInterface;
use SprykerEco\Zed\Algolia\Business\Saver\CmsPageSaver;
use SprykerEco\Zed\Algolia\Business\Saver\CmsPageSaverInterface;
use SprykerEco\Zed\Algolia\Business\Saver\ProductSaver;
use SprykerEco\Zed\Algolia\Business\Saver\ProductSaverInterface;
use SprykerEco\Zed\Algolia\Business\Updater\ProductUpdater;
use SprykerEco\Zed\Algolia\Business\Updater\ProductUpdaterInterface;
use SprykerEco\Zed\Algolia\Business\Validator\AdminApiKeyValidator;
use SprykerEco\Zed\Algolia\Business\Validator\ApiCredentialsValidator;
use SprykerEco\Zed\Algolia\Business\Validator\ApiCredentialsValidatorInterface;
use SprykerEco\Zed\Algolia\Business\Validator\ApiKeyValidatorInterface;
use SprykerEco\Zed\Algolia\Business\Validator\EnabledFeaturesValidator;
use SprykerEco\Zed\Algolia\Business\Validator\SearchOnlyApiKeyValidator;

/**
 * @method \SprykerEco\Zed\Algolia\AlgoliaConfig getConfig()
 */
class AlgoliaBusinessFactory extends AbstractBusinessFactory
{
    public function createProductExporter(): ProductExporterInterface
    {
        return new ProductExporter(
            $this->createProductIndexer(),
            $this->createProductSaver(),
            $this->createProductConcreteFilter(),
            $this->createProductDataFilterApplier(),
            $this->createAlgoliaConfigResolver(),
            $this->getProductFacade(),
        );
    }

    public function createApiCredentialsValidator(): ApiCredentialsValidatorInterface
    {
        return new ApiCredentialsValidator(
            [
                $this->createAdminApiKeyValidator(),
                $this->createSearchOnlyApiKeyValidator(),
                $this->createEnabledFeaturesValidator(),
            ],
        );
    }

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

    public function createProductSaver(): ProductSaverInterface
    {
        return new ProductSaver(
            $this->createSearchClientCreator(),
            $this->createSearchIndexClientCreator(),
            $this->createProductMapper(),
        );
    }

    public function createProductIndexer(): ProductIndexerInterface
    {
        return new ProductIndexer(
            $this->createProductMapper(),
            $this->createIndexNameResolver(),
        );
    }

    public function createIndexReader(): IndexReaderInterface
    {
        return new IndexReader($this->createIndexMapper());
    }

    public function createSuggestionIndexHandler(): SuggestionIndexHandlerInterface
    {
        return new SuggestionIndexHandler($this->getConfig());
    }

    public function createIndexNameResolver(): IndexNameResolverInterface
    {
        return new IndexNameResolver($this->getConfig());
    }

    public function createSearchIndexClientCreator(): SearchIndexClientCreatorInterface
    {
        return new SearchIndexClientCreator($this->createIndexConfigurator());
    }

    public function createSearchClientCreator(): SearchClientCreatorInterface
    {
        return new SearchClientCreator(
            $this->createAlgoliaConfigResolver(),
        );
    }

    public function createProductMapper(): ProductMapperInterface
    {
        return new ProductMapper();
    }

    public function createIndexMapper(): IndexMapperInterface
    {
        return new IndexMapper();
    }

    public function createIndexConfigurator(): IndexConfiguratorInterface
    {
        return new IndexConfigurator($this->createSuggestionIndexHandler(), $this->getConfig());
    }

    public function createProductConcreteFilter(): ProductConcreteFilterInterface
    {
        return new ProductConcreteFilter();
    }

    public function createAlgoliaConfigResolver(): AlgoliaConfigResolverInterface
    {
        return new AlgoliaConfigResolver($this->getConfig());
    }

    public function createAdminApiKeyValidator(): ApiKeyValidatorInterface
    {
        return new AdminApiKeyValidator(
            $this->createSearchClientCreator(),
        );
    }

    public function createSearchOnlyApiKeyValidator(): ApiKeyValidatorInterface
    {
        return new SearchOnlyApiKeyValidator(
            $this->createSearchClientCreator(),
        );
    }

    public function createEnabledFeaturesValidator(): ApiKeyValidatorInterface
    {
        return new EnabledFeaturesValidator(
            $this->createSearchClientCreator(),
        );
    }

    public function createProductDataFilterApplier(): ProductDataFilterApplierInterface
    {
        return new ProductDataFilterApplier(
            [
                new PriceProductDataFilter(),
            ],
        );
    }

    public function createCmsPageMapper(): CmsPageMapperInterface
    {
        return new CmsPageMapper();
    }

    public function createCmsPageIndexer(): CmsPageIndexerInterface
    {
        return new CmsPageIndexer(
            $this->createCmsPageMapper(),
            $this->createIndexNameResolver(),
        );
    }

    public function createCmsPageSaver(): CmsPageSaverInterface
    {
        return new CmsPageSaver(
            $this->createSearchClientCreator(),
            $this->createSearchIndexClientCreator(),
            $this->createIndexConfigurator(),
            $this->getConfig(),
        );
    }

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

    public function createCmsPagePublisher(): CmsPagePublisherInterface
    {
        return new CmsPagePublisher(
            $this->createCmsPageIndexer(),
            $this->createCmsPageSaver(),
            $this->createAlgoliaConfigResolver(),
        );
    }

    public function createCmsPageExporter(): CmsPageExporterInterface
    {
        return new CmsPageExporter(
            $this->createCmsPagePublisher(),
            $this->getCmsFacade(),
            $this->getCmsQueryContainer(),
            $this->createCmsPagePublishedTransferBuilder(),
        );
    }

    public function createCmsPagePublishedTransferBuilder(): CmsPagePublishedTransferBuilderInterface
    {
        return new CmsPagePublishedTransferBuilder(
            $this->getCmsFacade(),
        );
    }

    public function createAlgoliaEntityExporter(): AlgoliaEntityExporterInterface
    {
        return new AlgoliaEntityExporter(
            $this->getAlgoliaEntityExporterPlugins(),
            $this->getConfig(),
        );
    }

    /**
     * @return array<\SprykerEco\Zed\Algolia\Dependency\Plugin\AlgoliaEntityExporterPluginInterface>
     */
    public function getAlgoliaEntityExporterPlugins(): array
    {
        return $this->getProvidedDependency(AlgoliaDependencyProvider::PLUGINS_ALGOLIA_ENTITY_EXPORTER);
    }

    public function getProductFacade(): ProductFacadeInterface
    {
        return $this->getProvidedDependency(AlgoliaDependencyProvider::FACADE_PRODUCT);
    }

    public function getCmsFacade(): CmsFacadeInterface
    {
        return $this->getProvidedDependency(AlgoliaDependencyProvider::FACADE_CMS);
    }

    public function getCmsQueryContainer(): CmsQueryContainerInterface
    {
        return $this->getProvidedDependency(AlgoliaDependencyProvider::QUERY_CONTAINER_CMS);
    }
}
