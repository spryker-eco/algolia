<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Algolia\Communication\Constraint;

use Symfony\Component\Validator\Constraint;

class AlgoliaCredentialsRemovalConstraint extends Constraint implements AlgoliaSentinelConstraintInterface
{
    public const string INVALID_SENTINEL = '__ALGOLIA_CREDENTIALS_REMOVAL_INVALID__';

    public string $message = 'Cannot remove Algolia credentials while Algolia is selected as search provider. Please go to Catalog → Search or CMS → Search to disable it first.';

    public function getInvalidSentinel(): string
    {
        return static::INVALID_SENTINEL;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function validatedBy(): string
    {
        return AlgoliaCredentialsSentinelConstraintValidator::class;
    }
}
