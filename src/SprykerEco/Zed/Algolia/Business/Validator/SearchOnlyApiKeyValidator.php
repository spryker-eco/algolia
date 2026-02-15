<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Validator;

use Generated\Shared\Transfer\AlgoliaApiCredentialsTransfer;
use Generated\Shared\Transfer\AlgoliaApiCredentialsValidationTransfer;
use Generated\Shared\Transfer\AlgoliaConfigTransfer;
use SprykerEco\Zed\Algolia\Business\Api\Creator\SearchClientCreatorInterface;
use Throwable;

class SearchOnlyApiKeyValidator implements ApiKeyValidatorInterface
{
    public function __construct(
        protected SearchClientCreatorInterface $searchClientCreator
    ) {
    }

    public function validate(
        AlgoliaConfigTransfer $algoliaConfigTransfer,
        AlgoliaApiCredentialsValidationTransfer $algoliaApiCredentialsValidationTransfer
    ): AlgoliaApiCredentialsValidationTransfer {
        if (!$algoliaApiCredentialsValidationTransfer->getIsAccountIdValid()) {
            return $algoliaApiCredentialsValidationTransfer;
        }

        $algoliaAdminApiCredentialsTransfer = (new AlgoliaApiCredentialsTransfer())
            ->setApplicationId($algoliaConfigTransfer->getApplicationId())
            ->setApiKey($algoliaConfigTransfer->getAdminApiKey());

        $searchClient = $this->searchClientCreator->createSearchClientWithCredentials($algoliaAdminApiCredentialsTransfer);

        $algoliaSearchOnlyApiCredentialsTransfer = (new AlgoliaApiCredentialsTransfer())
            ->setApplicationId($algoliaConfigTransfer->getApplicationId())
            ->setApiKey($algoliaConfigTransfer->getSearchOnlyApiKey());

        try {
            $keyData = $searchClient->getApiKey($algoliaSearchOnlyApiCredentialsTransfer->getApiKey());
            if (!in_array('search', $keyData['acl'])) {
                $algoliaApiCredentialsValidationTransfer->setIsSearchOnlyApiKeyValid(false);

                return $algoliaApiCredentialsValidationTransfer;
            }

            $algoliaApiCredentialsValidationTransfer->setIsSearchOnlyApiKeyValid(true);
        } catch (Throwable $throwable) {
            return $algoliaApiCredentialsValidationTransfer->setIsSearchOnlyApiKeyValid(false);
        }

        return $algoliaApiCredentialsValidationTransfer;
    }
}
