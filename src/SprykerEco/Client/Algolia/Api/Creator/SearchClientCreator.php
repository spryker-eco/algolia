<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Algolia\Api\Creator;

use Algolia\AlgoliaSearch\SearchClient;
use Algolia\AlgoliaSearch\Support\UserAgent;
use Generated\Shared\Transfer\AlgoliaApiCredentialsTransfer;
use Generated\Shared\Transfer\AlgoliaConfigTransfer;
use SprykerEco\Client\Algolia\AlgoliaConfig;
use SprykerEco\Client\Algolia\Resolver\AlgoliaConfigResolverInterface;

class SearchClientCreator implements SearchClientCreatorInterface
{
    public function __construct(
        protected AlgoliaConfigResolverInterface $algoliaConfigResolver
    ) {
    }

    public function createSearchClientWithCredentials(AlgoliaApiCredentialsTransfer $algoliaCredentialsTransfer): SearchClient
    {
        UserAgent::addCustomUserAgent(AlgoliaConfig::USER_AGENT_SEGMENT_NAME, AlgoliaConfig::APP_VERSION);

        return SearchClient::create(
            $algoliaCredentialsTransfer->getApplicationId(),
            $algoliaCredentialsTransfer->getApiKey(),
        );
    }

    public function createSearchClientFromConfig(AlgoliaConfigTransfer $algoliaConfigTransfer, bool $isSearchOnly = false): SearchClient
    {
        $algoliaApiCredentialsTransfer = (new AlgoliaApiCredentialsTransfer())
            ->setApplicationId($algoliaConfigTransfer->getApplicationId())
            ->setApiKey($isSearchOnly ? $algoliaConfigTransfer->getSearchOnlyApiKey() : $algoliaConfigTransfer->getAdminApiKey());

        return $this->createSearchClientWithCredentials($algoliaApiCredentialsTransfer);
    }
}
