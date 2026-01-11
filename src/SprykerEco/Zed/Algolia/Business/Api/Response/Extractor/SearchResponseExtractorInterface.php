<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Api\Response\Extractor;

use Generated\Shared\Transfer\AlgoliaSearchResponseTransfer;
use Generated\Shared\Transfer\SearchRequestTransfer;

interface SearchResponseExtractorInterface
{
    /**
     * @var string
     */
    public const FIELD_HITS = 'hits';

    /**
     * @param \Generated\Shared\Transfer\SearchRequestTransfer $searchRequestTransfer
     *
     * @return bool
     */
    public function isApplicable(SearchRequestTransfer $searchRequestTransfer): bool;

    /**
     * @param \Generated\Shared\Transfer\AlgoliaSearchResponseTransfer $algoliaSearchResponseTransfer
     *
     * @return array
     */
    public function extract(AlgoliaSearchResponseTransfer $algoliaSearchResponseTransfer): array;

    /**
     * @return string
     */
    public function getName(): string;
}
