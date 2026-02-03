<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Algolia;

use Spryker\Client\Kernel\AbstractDependencyProvider;
use Spryker\Client\Kernel\Container;

/**
 * @method \SprykerEco\Client\Algolia\AlgoliaConfig getConfig()
 */
class AlgoliaDependencyProvider extends AbstractDependencyProvider
{
    public const string CLIENT_STORE = 'CLIENT_STORE';

    public const string CLIENT_LOCALE = 'CLIENT_LOCALE';

    public const string CLIENT_CUSTOMER = 'CLIENT_CUSTOMER';

    public function provideServiceLayerDependencies(Container $container): Container
    {
        $container = $this->addStoreClient($container);
        $container = $this->addLocaleClient($container);
        $container = $this->addCustomerClient($container);

        return $container;
    }

    protected function addCustomerClient(Container $container): Container
    {
        $container->set(static::CLIENT_CUSTOMER, function (Container $container) {
            return $container->getLocator()->customer()->client();
        });

        return $container;
    }

    protected function addLocaleClient(Container $container): Container
    {
        $container->set(static::CLIENT_LOCALE, function (Container $container) {
            return $container->getLocator()->locale()->client();
        });

        return $container;
    }

    protected function addStoreClient(Container $container): Container
    {
        $container->set(static::CLIENT_STORE, function (Container $container) {
            return $container->getLocator()->store()->client();
        });

        return $container;
    }
}
