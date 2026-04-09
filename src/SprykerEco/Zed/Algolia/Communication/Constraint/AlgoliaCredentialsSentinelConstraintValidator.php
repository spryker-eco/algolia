<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Algolia\Communication\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AlgoliaCredentialsSentinelConstraintValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof AlgoliaSentinelConstraintInterface) {
            throw new UnexpectedTypeException($constraint, AlgoliaSentinelConstraintInterface::class);
        }

        if ($value !== $constraint->getInvalidSentinel()) {
            return;
        }

        $this->context->buildViolation($constraint->message)->addViolation();
    }
}
