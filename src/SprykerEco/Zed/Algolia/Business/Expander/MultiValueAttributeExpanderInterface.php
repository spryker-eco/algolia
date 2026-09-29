<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Expander;

use Generated\Shared\Transfer\AlgoliaProductObjectTransfer;

interface MultiValueAttributeExpanderInterface
{
    public function expandAlgoliaProductObjectWithMultiValueAttributes(
        AlgoliaProductObjectTransfer $algoliaProductObjectTransfer
    ): AlgoliaProductObjectTransfer;
}
