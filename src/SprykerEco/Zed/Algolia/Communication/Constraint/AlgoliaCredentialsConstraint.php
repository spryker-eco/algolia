<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Algolia\Communication\Constraint;

use Symfony\Component\Validator\Constraint;

class AlgoliaCredentialsConstraint extends Constraint implements AlgoliaSentinelConstraintInterface
{
    public const string INVALID_SENTINEL = '__ALGOLIA_CREDENTIALS_INVALID__';

    public string $message = 'Algolia credentials are invalid. Application ID, Search-only API key, and Admin API key must all be provided and valid — partial configuration is not supported.';

    public function getInvalidSentinel(): string
    {
        return static::INVALID_SENTINEL;
    }

    public function validatedBy(): string
    {
        return AlgoliaCredentialsSentinelConstraintValidator::class;
    }
}
