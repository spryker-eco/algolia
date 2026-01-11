<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Algolia\Business\Api\SearchParameters\Expander;

use Generated\Shared\Transfer\SearchRequestTransfer;

interface SearchParametersExpanderInterface
{
    /**
     * @param string $sourceIdentifier
     *
     * @return bool
     */
    public function isApplicable(string $sourceIdentifier): bool;

    /**
     * @param string $filters
     * @param \Generated\Shared\Transfer\SearchRequestTransfer $searchRequestTransfer
     *
     * @return string
     */
    public function expandFilters(string $filters, SearchRequestTransfer $searchRequestTransfer): string;

    /**
     * @param array<string, mixed> $additionalParameters
     * @param \Generated\Shared\Transfer\SearchRequestTransfer $searchRequestTransfer
     *
     * @return array<string, mixed>
     */
    public function expandSourceIdentifierParameters(array $additionalParameters, SearchRequestTransfer $searchRequestTransfer): array;
}
