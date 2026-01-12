<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Validator;

use Algolia\AlgoliaSearch\Exceptions\UnreachableException;
use Generated\Shared\Transfer\AlgoliaApiCredentialsTransfer;
use Generated\Shared\Transfer\AlgoliaApiCredentialsValidationTransfer;
use Generated\Shared\Transfer\AlgoliaConfigTransfer;
use SprykerEco\Zed\Algolia\Business\Api\Creator\SearchClientCreatorInterface;
use SprykerEco\Zed\Algolia\Business\Mapper\CredentialsMapperInterface;
use Throwable;

class AdminApiKeyValidator implements ApiKeyValidatorInterface
{
    /**
     * @var \SprykerEco\Zed\Algolia\Business\Mapper\CredentialsMapperInterface
     */
    protected $credentialsMapper;

    /**
     * @var \SprykerEco\Zed\Algolia\Business\Api\Creator\SearchClientCreatorInterface
     */
    protected $searchClientCreator;

    public function __construct(
        CredentialsMapperInterface $credentialsMapper,
        SearchClientCreatorInterface $searchClientCreator
    ) {
        $this->credentialsMapper = $credentialsMapper;
        $this->searchClientCreator = $searchClientCreator;
    }

    public function validate(
        AlgoliaConfigTransfer $algoliaConfigTransfer,
        AlgoliaApiCredentialsValidationTransfer $algoliaApiCredentialsValidationTransfer
    ): AlgoliaApiCredentialsValidationTransfer {
        $algoliaApiCredentialsTransfer = $this->credentialsMapper
            ->mapAlgoliaAdminCredentialsToAlgoliaApiCredentialsTransfer(
                $algoliaConfigTransfer,
                new AlgoliaApiCredentialsTransfer(),
            );

        $searchClient = $this->searchClientCreator->createSearchClient($algoliaApiCredentialsTransfer);

        try {
            $keyData = $searchClient->getApiKey($algoliaApiCredentialsTransfer->getApiKey());
            $algoliaApiCredentialsValidationTransfer->setIsAccountIdValid(true);

            if (!in_array('addObject', $keyData['acl'])) {
                $algoliaApiCredentialsValidationTransfer->setIsAdminApiKeyValid(false);

                return $algoliaApiCredentialsValidationTransfer;
            }

            $algoliaApiCredentialsValidationTransfer->setIsAdminApiKeyValid(true);
        } catch (UnreachableException $exception) {
            // when ApplicationID is incorrect Algolia returns UnreachableException
            $algoliaApiCredentialsValidationTransfer->setIsAccountIdValid(false);
        } catch (Throwable $throwable) {
            // in all other cases API key is invalid
            $algoliaApiCredentialsValidationTransfer->setIsAdminApiKeyValid(false);
        }

        return $algoliaApiCredentialsValidationTransfer;
    }
}
