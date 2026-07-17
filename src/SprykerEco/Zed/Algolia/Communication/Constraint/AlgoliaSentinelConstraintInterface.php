<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Algolia\Communication\Constraint;

interface AlgoliaSentinelConstraintInterface
{
    /**
     * Returns the sentinel value that marks a field as invalid.
     */
    public function getInvalidSentinel(): string;

    /**
     * Returns the violation message to use when the sentinel value is detected.
     */
    public function getMessage(): string;
}
