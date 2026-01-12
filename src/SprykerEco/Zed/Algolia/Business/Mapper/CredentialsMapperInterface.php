<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Mapper;

use Generated\Shared\Transfer\AlgoliaApiCredentialsTransfer;
use Generated\Shared\Transfer\AlgoliaConfigTransfer;

interface CredentialsMapperInterface
{
    /**
     * @return \Generated\Shared\Transfer\AlgoliaApiCredentialsTransfer
     */
    public function mapAlgoliaAdminCredentialsToAlgoliaApiCredentialsTransfer(
        AlgoliaConfigTransfer $algoliaConfigTransfer,
        AlgoliaApiCredentialsTransfer $algoliaApiCredentialsTransfer
    ): AlgoliaApiCredentialsTransfer;

    /**
     * @return \Generated\Shared\Transfer\AlgoliaApiCredentialsTransfer
     */
    public function mapAlgoliaSearchOnlyCredentialsToAlgoliaApiCredentialsTransfer(
        AlgoliaConfigTransfer $algoliaConfigTransfer,
        AlgoliaApiCredentialsTransfer $algoliaApiCredentialsTransfer
    ): AlgoliaApiCredentialsTransfer;
}
