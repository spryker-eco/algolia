<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Shared\Algolia\Enum;

enum AlgoliaEntityNameEnum: string
{
    case PRODUCT = 'product';
    case CMS_PAGE = 'cms-page';

    /**
     * @return array<string>
     */
    public static function getAllValues(): array
    {
        return array_map(
            static fn (self $enum): string => $enum->value,
            static::cases(),
        );
    }
}
