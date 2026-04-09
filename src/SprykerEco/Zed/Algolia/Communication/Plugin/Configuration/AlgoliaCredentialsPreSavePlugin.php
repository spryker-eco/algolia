<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Algolia\Communication\Plugin\Configuration;

use Generated\Shared\Transfer\ConfigurationValueCollectionRequestTransfer;
use Spryker\Zed\ConfigurationExtension\Dependency\Plugin\ConfigurationValuePreSavePluginInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;

/**
 * @method \SprykerEco\Zed\Algolia\Business\AlgoliaBusinessFactory getBusinessFactory()
 * @method \SprykerEco\Zed\Algolia\AlgoliaConfig getConfig()
 * @method \SprykerEco\Zed\Algolia\Communication\AlgoliaCommunicationFactory getFactory()
 * @method \SprykerEco\Zed\Algolia\Business\AlgoliaFacadeInterface getFacade()
 */
class AlgoliaCredentialsPreSavePlugin extends AbstractPlugin implements ConfigurationValuePreSavePluginInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function preSave(
        ConfigurationValueCollectionRequestTransfer $requestTransfer,
    ): ConfigurationValueCollectionRequestTransfer {
        return $this->getBusinessFactory()
            ->createCredentialsPreSaveHandler()
            ->handleCredentialsPreSave($requestTransfer);
    }
}
