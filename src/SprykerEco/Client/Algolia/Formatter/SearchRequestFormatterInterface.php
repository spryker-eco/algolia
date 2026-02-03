<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Algolia\Formatter;

use Generated\Shared\Transfer\SearchRequestTransfer;
use Spryker\Client\SearchExtension\Dependency\Plugin\QueryInterface;

interface SearchRequestFormatterInterface
{
    /**
     * @param array<string, mixed> $requestParameters
     *
     * @return \Generated\Shared\Transfer\SearchRequestTransfer
     */
    public function formatRequest(QueryInterface $searchQuery, array $requestParameters): SearchRequestTransfer;

    /**
     * @param array<string, mixed> $requestParameters
     *
     * @return \Generated\Shared\Transfer\SearchRequestTransfer
     */
    public function formatSuggestionRequest(QueryInterface $searchQuery, array $requestParameters): SearchRequestTransfer;
}
