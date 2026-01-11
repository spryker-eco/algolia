<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Validator;

use Generated\Shared\Transfer\AlgoliaApiCredentialsValidationTransfer;
use Generated\Shared\Transfer\AlgoliaConfigTransfer;

class ApiCredentialsValidator
{
    /**
     * @var array<\SprykerEco\Zed\Algolia\Business\Validator\ApiKeyValidatorInterface>
     */
    protected $apiCredentialsValidators;

    /**
     * @param array<\SprykerEco\Zed\Algolia\Business\Validator\ApiKeyValidatorInterface> $apiCredentialsValidatorPlugins
     */
    public function __construct(array $apiCredentialsValidatorPlugins)
    {
        $this->apiCredentialsValidators = $apiCredentialsValidatorPlugins;
    }

    /**
     * @param \Generated\Shared\Transfer\AlgoliaConfigTransfer $algoliaConfigTransfer
     *
     * @return \Generated\Shared\Transfer\AlgoliaApiCredentialsValidationTransfer
     */
    public function validate(AlgoliaConfigTransfer $algoliaConfigTransfer): AlgoliaApiCredentialsValidationTransfer
    {
        $algoliaApiCredentialsValidationTransfer = (new AlgoliaApiCredentialsValidationTransfer())
            ->setIsAccountIdValid(false)
            ->setIsAdminApiKeyValid(false)
            ->setIsSearchOnlyApiKeyValid(false);

        foreach ($this->apiCredentialsValidators as $apiCredentialsValidatorPlugin) {
            $algoliaApiCredentialsValidationTransfer = $apiCredentialsValidatorPlugin
                ->validate(
                    $algoliaConfigTransfer,
                    $algoliaApiCredentialsValidationTransfer,
                );
        }

        return $algoliaApiCredentialsValidationTransfer;
    }
}
