<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Client\Algolia\Api\SearchParameters\Expander;

use Generated\Shared\Transfer\SearchRequestTransfer;
use SprykerEco\Client\Algolia\AlgoliaConfig;
use SprykerEco\Shared\Algolia\Enum\AlgoliaCmsPageObjectEnum;
use SprykerEco\Shared\Algolia\Enum\AlgoliaEntityNameEnum;

class CmsPageSearchParametersExpander implements SearchParametersExpanderInterface
{
    public function __construct(protected AlgoliaConfig $config)
    {
    }

    public function isApplicable(string $sourceIdentifier): bool
    {
        return $sourceIdentifier === AlgoliaEntityNameEnum::CMS_PAGE->value;
    }

    public function expandFilters(string $filters, SearchRequestTransfer $searchRequestTransfer): string
    {
        $cmsPageFilters = $this->buildCmsPageFilters($searchRequestTransfer);
        $allFilters = array_filter([$filters, ...$cmsPageFilters]);

        return implode(' AND ', $allFilters);
    }

    /**
     * Build all CMS page specific filters.
     *
     * @return array<string>
     */
    protected function buildCmsPageFilters(SearchRequestTransfer $searchRequestTransfer): array
    {
        return [
        $this->getCmsPageStoreFilter($searchRequestTransfer),
        $this->getValidityFilters(),
        ];
    }

    /**
     * @param array<string, mixed> $additionalParameters

     * @return array<string, mixed>
     */
    public function expandSourceIdentifierParameters(array $additionalParameters, SearchRequestTransfer $searchRequestTransfer): array
    {
        return array_merge($additionalParameters, [
            'attributesToSnippet' => [AlgoliaCmsPageObjectEnum::CONTENT->value . ':50'],
            'snippetEllipsisText' => '...',
            'attributesToHighlight' => [AlgoliaCmsPageObjectEnum::NAME->value],
        ]);
    }

    protected function getCmsPageStoreFilter(SearchRequestTransfer $searchRequestTransfer): string
    {
        return sprintf('%s:%s', AlgoliaCmsPageObjectEnum::STORE->value, $searchRequestTransfer->getStoreName());
    }

    protected function getValidityFilters(): string
    {
        $currentTimestamp = time();

        // Separate filters for each field to comply with Algolia restrictions
        $validFromFilter = sprintf(
            '%s = 0 OR %s <= %d',
            AlgoliaCmsPageObjectEnum::VALID_FROM->value,
            AlgoliaCmsPageObjectEnum::VALID_FROM->value,
            $currentTimestamp,
        );

        $validToFilter = sprintf(
            '%s = 0 OR %s >= %d',
            AlgoliaCmsPageObjectEnum::VALID_TO->value,
            AlgoliaCmsPageObjectEnum::VALID_TO->value,
            $currentTimestamp,
        );

        return sprintf('(%s) AND (%s)', $validFromFilter, $validToFilter);
    }
}
