<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Client\Algolia\Api\Response\Builder;

use Generated\Shared\Transfer\AlgoliaSearchResponseTransfer;
use Generated\Shared\Transfer\GlueErrorTransfer;
use Generated\Shared\Transfer\SearchRequestTransfer;
use Generated\Shared\Transfer\SuggestionsMatchesCollectionTransfer;
use Generated\Shared\Transfer\SuggestionsSearchResponseTransfer;
use SprykerEco\Client\Algolia\Api\Response\Extractor\SearchResponseExtractorInterface;
use SprykerEco\Shared\Algolia\Enum\AlgoliaEntityNameEnum;

class SuggestionsSearchResponseBuilder implements SuggestionsSearchResponseBuilderInterface
{
    /**
     * @param array<\SprykerEco\Client\Algolia\Api\Response\Extractor\SearchResponseExtractorInterface> $suggestionsExtractors
     */
    public function __construct(
        protected SearchResponseExtractorInterface $completionsExtractor,
        protected array $suggestionsExtractors,
        protected SearchResponseExtractorInterface $categoryExtractor
    ) {
    }

    public function buildSuccessfulResponse(
        AlgoliaSearchResponseTransfer $algoliaSearchResponseTransfer,
        SearchRequestTransfer $searchRequestTransfer
    ): SuggestionsSearchResponseTransfer {
        $suggestionsMatchesCollectionTransfer = (new SuggestionsMatchesCollectionTransfer());
        $matchedItemsBySourceIdentifier = [];

        foreach ($this->suggestionsExtractors as $suggestionsExtractor) {
            if ($suggestionsExtractor->getName() === AlgoliaEntityNameEnum::PRODUCT->value) {
                $suggestionsMatchesCollectionTransfer->fromArray($suggestionsExtractor->extract($algoliaSearchResponseTransfer), true);

                continue;
            }
            $matchedItemsBySourceIdentifier[$suggestionsExtractor->getName()] = $suggestionsExtractor->extract($algoliaSearchResponseTransfer);
        }

        $notProcessedTypes = array_diff(
            array_keys($algoliaSearchResponseTransfer->getSearchResults()),
            array_merge(['suggestions', 'completions', 'categories'], array_keys($matchedItemsBySourceIdentifier)),
        );

        foreach ($notProcessedTypes as $type) {
            $matchedItemsBySourceIdentifier[$type] = $algoliaSearchResponseTransfer->getSearchResults()[$type][SearchResponseExtractorInterface::FIELD_HITS] ?? [];
        }

        return (new SuggestionsSearchResponseTransfer())
            ->setIsSuccessful(true)
            ->setCompletions($this->completionsExtractor->extract($algoliaSearchResponseTransfer))
            ->setMatches($suggestionsMatchesCollectionTransfer->getMatches())
            ->setMatchedItems($suggestionsMatchesCollectionTransfer->getMatchedItems())
            ->setMatchedItemsBySourceIdentifiers($matchedItemsBySourceIdentifier)
            ->setCategories(
                array_unique(array_merge(
                    $this->categoryExtractor->extract($algoliaSearchResponseTransfer),
                    $suggestionsMatchesCollectionTransfer->getCategories(),
                )),
            );
    }

    public function buildUnsuccessfulResponse(string $errorMessage, int $statusCode): SuggestionsSearchResponseTransfer
    {
        return (new SuggestionsSearchResponseTransfer())
            ->setIsSuccessful(false)
            ->setStatusCode($statusCode)
            ->addError(
                (new GlueErrorTransfer())
                    ->setStatus($statusCode)
                    ->setMessage($errorMessage),
            );
    }
}
