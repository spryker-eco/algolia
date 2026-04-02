<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Algolia\Communication\Constraint;

use Spryker\Zed\Kernel\Communication\Validator\AbstractConstraintValidator;
use SprykerEco\Zed\Algolia\AlgoliaConfig;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @method \SprykerEco\Zed\Algolia\Communication\AlgoliaCommunicationFactory getFactory()
 */
class AlgoliaSearchProviderConstraintValidator extends AbstractConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof AlgoliaSearchProviderConstraint) {
            throw new UnexpectedTypeException($constraint, AlgoliaSearchProviderConstraint::class);
        }

        if ($value !== AlgoliaConfig::SEARCH_PROVIDER_ALGOLIA) {
            return;
        }

        if ($this->isAlgoliaActive()) {
            return;
        }

        $this->context->buildViolation($constraint->message)->addViolation();
    }

    protected function isAlgoliaActive(): bool
    {
        return $this->getFactory()->getConfig()->getIsActive();
    }
}
