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
        $multiValueAttributeDelimiters = $this->algoliaConfig->getMultiValueProductAttributeDelimiters();

        if ($multiValueAttributeDelimiters === []) {
            return $algoliaProductObjectTransfer;
        }

        $attributes = $algoliaProductObjectTransfer->getAttributes();

        foreach ($multiValueAttributeDelimiters as $attributeName => $delimiter) {
            if (
                !isset($attributes[$attributeName])
                || !is_string($attributes[$attributeName])
                || !str_contains($attributes[$attributeName], $delimiter)
            ) {
                continue;
            }

            $attributes[$attributeName] = array_map('trim', explode($delimiter, $attributes[$attributeName]));
        }

        return $algoliaProductObjectTransfer->setAttributes($attributes);
    }
}
