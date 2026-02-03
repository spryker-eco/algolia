<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Algolia\Formatter;

use Generated\Shared\Transfer\SearchResponseTransfer;
use Generated\Shared\Transfer\SuggestionsSearchResponseTransfer;

interface SearchResponseFormatterInterface
{
    /**
     * @param array<\Spryker\Client\SearchExtension\Dependency\Plugin\ResultFormatterPluginInterface> $resultFormatters
     * @param array<string, mixed> $requestParameters
     *
     * @return array<string, mixed>
     */
    public function format(
        SearchResponseTransfer $algoliaSearchResponseTransfer,
        array $resultFormatters = [],
        array $requestParameters = []
    ): array;

    /**
     * @param array<\Spryker\Client\SearchExtension\Dependency\Plugin\ResultFormatterPluginInterface> $resultFormatters
     * @param array<string, mixed> $requestParameters
     *
     * @return array<string, mixed>
     */
    public function formatSuggestion(
        SuggestionsSearchResponseTransfer $algoliaSuggestionsSearchResponseTransfer,
        array $resultFormatters = [],
        array $requestParameters = []
    ): array;
}
