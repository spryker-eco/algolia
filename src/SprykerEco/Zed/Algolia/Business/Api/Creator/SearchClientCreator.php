<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Api\Creator;

use Algolia\AlgoliaSearch\SearchClient;
use Algolia\AlgoliaSearch\Support\UserAgent;
use Generated\Shared\Transfer\AlgoliaApiCredentialsTransfer;
use Generated\Shared\Transfer\AlgoliaConfigTransfer;
use SprykerEco\Zed\Algolia\AlgoliaConfig;
use SprykerEco\Zed\Algolia\Business\Mapper\CredentialsMapperInterface;
use SprykerEco\Zed\Algolia\Business\Resolver\AlgoliaConfigResolver;

class SearchClientCreator implements SearchClientCreatorInterface
{
    /**
     * @param \SprykerEco\Zed\Algolia\Business\Resolver\AlgoliaConfigResolver $algoliaConfigResolver
     * @param \SprykerEco\Zed\Algolia\Business\Mapper\CredentialsMapperInterface $credentialsMapper
     */
    public function __construct(
        protected AlgoliaConfigResolver $algoliaConfigResolver,
        protected CredentialsMapperInterface $credentialsMapper
    ) {
    }

    /**
     * @param \Generated\Shared\Transfer\AlgoliaApiCredentialsTransfer $algoliaCredentialsTransfer
     *
     * @return \Algolia\AlgoliaSearch\SearchClient
     */
    public function createSearchClient(AlgoliaApiCredentialsTransfer $algoliaCredentialsTransfer): SearchClient
    {
        UserAgent::addCustomUserAgent(AlgoliaConfig::USER_AGENT_SEGMENT_NAME, AlgoliaConfig::APP_VERSION);

        return SearchClient::create(
            $algoliaCredentialsTransfer->getApplicationId(),
            $algoliaCredentialsTransfer->getApiKey(),
        );
    }

    /**
     * @param \Generated\Shared\Transfer\AlgoliaConfigTransfer $algoliaConfigTransfer
     * @param bool $isSearchOnly
     *
     * @return \Algolia\AlgoliaSearch\SearchClient
     */
    public function createSearchClientByStoreReference(AlgoliaConfigTransfer $algoliaConfigTransfer, bool $isSearchOnly = false): SearchClient
    {
        $algoliaApiCredentialsTransfer = $this->findCredentialsByStoreReference($algoliaConfigTransfer, $isSearchOnly);

        return $this->createSearchClient($algoliaApiCredentialsTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\AlgoliaConfigTransfer $algoliaConfigTransfer
     * @param bool $isSearchOnly
     *
     * @return \Generated\Shared\Transfer\AlgoliaApiCredentialsTransfer|null
     */
    protected function findCredentialsByStoreReference(AlgoliaConfigTransfer $algoliaConfigTransfer, bool $isSearchOnly = false): ?AlgoliaApiCredentialsTransfer
    {
        if ($isSearchOnly) {
            return $this->credentialsMapper->mapAlgoliaSearchOnlyCredentialsToAlgoliaApiCredentialsTransfer(
                $algoliaConfigTransfer,
                new AlgoliaApiCredentialsTransfer(),
            );
        } else {
            return $this->credentialsMapper->mapAlgoliaAdminCredentialsToAlgoliaApiCredentialsTransfer(
                $algoliaConfigTransfer,
                new AlgoliaApiCredentialsTransfer(),
            );
        }
    }
}
