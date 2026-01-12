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
use SprykerEco\Zed\Algolia\Business\Resolver\AlgoliaConfigResolverInterface;

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
