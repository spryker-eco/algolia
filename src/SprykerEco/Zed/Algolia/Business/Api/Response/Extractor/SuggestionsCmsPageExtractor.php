<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Api\Response\Extractor;

use Generated\Shared\Transfer\AlgoliaSearchResponseTransfer;
use SprykerEco\Shared\Algolia\Enum\AlgoliaEntityNameEnum;

class SuggestionsCmsPageExtractor extends CmsPageExtractor implements SearchResponseExtractorInterface
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function extract(AlgoliaSearchResponseTransfer $algoliaSearchResponseTransfer): array
    {
        return $this->simplifyHits($this->getHits($algoliaSearchResponseTransfer->getSearchResults()[AlgoliaEntityNameEnum::CMS_PAGE->value] ?? []));
    }
}
