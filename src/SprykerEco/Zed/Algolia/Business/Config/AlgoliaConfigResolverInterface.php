<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Config;

use Generated\Shared\Transfer\AlgoliaConfigTransfer;

interface AlgoliaConfigResolverInterface
{
    public function getConfig(): AlgoliaConfigTransfer;
}
