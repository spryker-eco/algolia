<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Algolia\Communication\Constraint;

use Symfony\Component\Validator\Constraint;

class AlgoliaSearchProviderConstraint extends Constraint
{
    public string $message = 'Cannot enable Algolia search without configured credentials. Please go to Integrations → Algolia to save your credentials first.';

    public function validatedBy(): string
    {
        return AlgoliaSearchProviderConstraintValidator::class;
    }
}
