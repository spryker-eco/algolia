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
use SprykerEco\Zed\Algolia\Business\Mapper\CredentialsMapperInterface;
use Throwable;

class SearchOnlyApiKeyValidator implements ApiKeyValidatorInterface
{
    /**
     * @var \SprykerEco\Zed\Algolia\Business\Mapper\CredentialsMapperInterface
     */
    protected $credentialsMapper;

    /**
     * @var \SprykerEco\Zed\Algolia\Business\Api\Creator\SearchClientCreatorInterface
     */
    protected $searchClientCreator;

    /**
     * @param \SprykerEco\Zed\Algolia\Business\Mapper\CredentialsMapperInterface $credentialsMapper
     * @param \SprykerEco\Zed\Algolia\Business\Api\Creator\SearchClientCreatorInterface $searchClientCreator
     */
    public function __construct(
        CredentialsMapperInterface $credentialsMapper,
        SearchClientCreatorInterface $searchClientCreator
    ) {
        $this->credentialsMapper = $credentialsMapper;
        $this->searchClientCreator = $searchClientCreator;
    }

    /**
     * @param \Generated\Shared\Transfer\AlgoliaConfigTransfer $algoliaConfigTransfer
     * @param \Generated\Shared\Transfer\AlgoliaApiCredentialsValidationTransfer $algoliaApiCredentialsValidationTransfer
     *
     * @return \Generated\Shared\Transfer\AlgoliaApiCredentialsValidationTransfer
     */
    public function validate(
        AlgoliaConfigTransfer $algoliaConfigTransfer,
        AlgoliaApiCredentialsValidationTransfer $algoliaApiCredentialsValidationTransfer
    ): AlgoliaApiCredentialsValidationTransfer {
        if (!$algoliaApiCredentialsValidationTransfer->getIsAccountIdValid()) {
            return $algoliaApiCredentialsValidationTransfer;
        }

        $algoliaApiCredentialsTransfer = $this->credentialsMapper
            ->mapAlgoliaAdminCredentialsToAlgoliaApiCredentialsTransfer(
                $algoliaConfigTransfer,
                new AlgoliaApiCredentialsTransfer(),
            );

        $searchClient = $this->searchClientCreator->createSearchClient($algoliaApiCredentialsTransfer);

        $algoliaSearchOnlyApiCredentialsTransfer = $this->credentialsMapper
            ->mapAlgoliaSearchOnlyCredentialsToAlgoliaApiCredentialsTransfer(
                $algoliaConfigTransfer,
                new AlgoliaApiCredentialsTransfer(),
            );

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
