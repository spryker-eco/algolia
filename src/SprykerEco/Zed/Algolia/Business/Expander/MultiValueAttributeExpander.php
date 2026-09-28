<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Expander;

use Generated\Shared\Transfer\AlgoliaProductObjectTransfer;
use SprykerEco\Zed\Algolia\AlgoliaConfig;

class MultiValueAttributeExpander implements MultiValueAttributeExpanderInterface
{
    public function __construct(protected AlgoliaConfig $algoliaConfig)
    {
    }

    public function expandAlgoliaProductObjectWithMultiValueAttributes(
        AlgoliaProductObjectTransfer $algoliaProductObjectTransfer,
    ): AlgoliaProductObjectTransfer {
        $multiValueAttributeNames = $this->algoliaConfig->getMultiValueProductAttributeNames();

        if ($multiValueAttributeNames === []) {
            return $algoliaProductObjectTransfer;
        }

        $attributes = $algoliaProductObjectTransfer->getAttributes();
        $delimiter = $this->algoliaConfig->getMultiValueProductAttributeDelimiter();

        foreach ($multiValueAttributeNames as $multiValueAttributeName) {
            // Values that are not strings are already split, e.g. by a `multiselect` product attribute.
            if (!isset($attributes[$multiValueAttributeName]) || !is_string($attributes[$multiValueAttributeName])) {
                continue;
            }

            $attributes[$multiValueAttributeName] = array_map(
                'trim',
                explode($delimiter, $attributes[$multiValueAttributeName]),
            );
        }

        return $algoliaProductObjectTransfer->setAttributes($attributes);
    }
}
