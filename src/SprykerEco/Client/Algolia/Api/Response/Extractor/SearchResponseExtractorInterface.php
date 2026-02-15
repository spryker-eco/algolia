<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Algolia\Api\Response\Extractor;

use Generated\Shared\Transfer\AlgoliaSearchResponseTransfer;
use Generated\Shared\Transfer\SearchRequestTransfer;

interface SearchResponseExtractorInterface
{
    /**
     * @var string
     */
    public const FIELD_HITS = 'hits';

    /**
     * @return bool
     */
    public function isApplicable(SearchRequestTransfer $searchRequestTransfer): bool;

    /**
     * @return array
     */
    public function extract(AlgoliaSearchResponseTransfer $algoliaSearchResponseTransfer): array;

    /**
     * @return string
     */
    public function getName(): string;
}
