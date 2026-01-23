<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Communication;

use Spryker\Zed\Kernel\Communication\AbstractCommunicationFactory;
use Spryker\Zed\Product\Business\ProductFacadeInterface;
use SprykerEco\Zed\Algolia\AlgoliaDependencyProvider;

/**
 * @method \SprykerEco\Zed\Algolia\AlgoliaConfig getConfig()
 * @method \SprykerEco\Zed\Algolia\Business\AlgoliaFacadeInterface getFacade()
 */
class AlgoliaCommunicationFactory extends AbstractCommunicationFactory
{
    public function getProductFacade(): ProductFacadeInterface
    {
        return $this->getProvidedDependency(AlgoliaDependencyProvider::FACADE_PRODUCT);
    }
}
