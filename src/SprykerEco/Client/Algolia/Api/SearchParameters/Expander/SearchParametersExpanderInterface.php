<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Client\Algolia\Api\SearchParameters\Expander;

use Generated\Shared\Transfer\SearchRequestTransfer;

interface SearchParametersExpanderInterface
{
    /**
     * @return bool
     */
    public function isApplicable(string $sourceIdentifier): bool;

    /**
     * @return string
     */
    public function expandFilters(string $filters, SearchRequestTransfer $searchRequestTransfer): string;

    /**
     * @param array<string, mixed> $additionalParameters

     * @return array<string, mixed>
     */
    public function expandSourceIdentifierParameters(array $additionalParameters, SearchRequestTransfer $searchRequestTransfer): array;
}
