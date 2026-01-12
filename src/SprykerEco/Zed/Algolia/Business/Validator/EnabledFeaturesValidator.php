<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Validator;

use Algolia\AlgoliaSearch\Exceptions\BadRequestException;
use Generated\Shared\Transfer\AlgoliaApiCredentialsTransfer;
use Generated\Shared\Transfer\AlgoliaApiCredentialsValidationTransfer;
use Generated\Shared\Transfer\AlgoliaConfigTransfer;
use SprykerEco\Zed\Algolia\AlgoliaConfig;
use SprykerEco\Zed\Algolia\Business\Api\Creator\SearchClientCreatorInterface;

class EnabledFeaturesValidator implements ApiKeyValidatorInterface
{
    public function __construct(
        protected SearchClientCreatorInterface $searchClientCreator
    ) {
    }

    public function validate(
        AlgoliaConfigTransfer $algoliaConfigTransfer,
        AlgoliaApiCredentialsValidationTransfer $algoliaApiCredentialsValidationTransfer
    ): AlgoliaApiCredentialsValidationTransfer {
        if (!$algoliaApiCredentialsValidationTransfer->getIsAccountIdValid() || !$algoliaApiCredentialsValidationTransfer->getIsAdminApiKeyValid()) {
            return $algoliaApiCredentialsValidationTransfer;
        }

        $algoliaApiCredentialsTransfer = (new AlgoliaApiCredentialsTransfer())
            ->setApplicationId($algoliaConfigTransfer->getApplicationId())
            ->setApiKey($algoliaConfigTransfer->getAdminApiKey());

        $searchClient = $this->searchClientCreator->createSearchClientWithCredentials($algoliaApiCredentialsTransfer);

        try {
            $testIndex = $searchClient->initIndex('test');
            $testIndex->setSettings(['enablePersonalization' => true])->wait();
            $testIndex->delete();

            $algoliaConfigTransfer->setEnabledFeatures([AlgoliaConfig::FEATURE_PERSONALIZATION]);
        } catch (BadRequestException $e) {
            // remove personalization if it was there previously
            $algoliaConfigTransfer->setEnabledFeatures([]);
        }

        return $algoliaApiCredentialsValidationTransfer;
    }
}
