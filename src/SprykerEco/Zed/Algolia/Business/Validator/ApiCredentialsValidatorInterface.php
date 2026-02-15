<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Validator;

use Generated\Shared\Transfer\AlgoliaApiCredentialsValidationTransfer;
use Generated\Shared\Transfer\AlgoliaConfigTransfer;

interface ApiCredentialsValidatorInterface
{
    public function validate(AlgoliaConfigTransfer $algoliaConfigTransfer): AlgoliaApiCredentialsValidationTransfer;
}
