<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia;

use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;
use SprykerEco\Zed\Algolia\Communication\Plugin\Algolia\CmsPageAlgoliaEntityExporterPlugin;
use SprykerEco\Zed\Algolia\Communication\Plugin\Algolia\ProductAlgoliaEntityExporterPlugin;

/**
 * @method \SprykerEco\Zed\Algolia\AlgoliaConfig getConfig()
 */
class AlgoliaDependencyProvider extends AbstractBundleDependencyProvider
{
    public const string PLUGINS_ALGOLIA_ENTITY_EXPORTER = 'PLUGINS_ALGOLIA_ENTITY_EXPORTER';

    public const FACADE_PRODUCT = 'FACADE_PRODUCT';

    public const FACADE_CMS = 'FACADE_CMS';

    public const string QUERY_CONTAINER_CMS = 'QUERY_CONTAINER_CMS';

    public function provideBusinessLayerDependencies(Container $container): Container
    {
        $container = parent::provideBusinessLayerDependencies($container);
        $container = $this->addAlgoliaEntityExporterPlugins($container);
        $container = $this->addProductFacade($container);
        $container = $this->addCmsFacade($container);
        $container = $this->addCmsQueryContainer($container);

        return $container;
    }

    public function provideCommunicationLayerDependencies(Container $container): Container
    {
        $container = parent::provideCommunicationLayerDependencies($container);
        $container = $this->addProductFacade($container);

        return $container;
    }

    protected function addAlgoliaEntityExporterPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_ALGOLIA_ENTITY_EXPORTER, function () {
            return $this->getAlgoliaEntityExporterPlugins();
        });

        return $container;
    }

    /**
     * @return array<\SprykerEco\Zed\Algolia\Dependency\Plugin\AlgoliaEntityExporterPluginInterface>
     */
    protected function getAlgoliaEntityExporterPlugins(): array
    {
        return [
             new ProductAlgoliaEntityExporterPlugin(),
             new CmsPageAlgoliaEntityExporterPlugin(),
        ];
    }

    protected function addProductFacade(Container $container): Container
    {
        $container->set(static::FACADE_PRODUCT, function (Container $container) {
            return $container->getLocator()->product()->facade();
        });

        return $container;
    }

    protected function addCmsFacade(Container $container): Container
    {
        $container->set(static::FACADE_CMS, function (Container $container) {
            return $container->getLocator()->cms()->facade();
        });

        return $container;
    }

    protected function addCmsQueryContainer(Container $container): Container
    {
        $container->set(static::QUERY_CONTAINER_CMS, function (Container $container) {
            return $container->getLocator()->cms()->queryContainer();
        });

        return $container;
    }
}
