<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Algolia\Business\Api\Response\Builder;

use Generated\Shared\Transfer\AlgoliaSearchResponseTransfer;
use Generated\Shared\Transfer\GlueErrorTransfer;
use Generated\Shared\Transfer\SearchRequestTransfer;
use Generated\Shared\Transfer\SuggestionsMatchesCollectionTransfer;
use Generated\Shared\Transfer\SuggestionsSearchResponseTransfer;
use SprykerEco\Shared\Algolia\Enum\AlgoliaEntityNameEnum;
use SprykerEco\Zed\Algolia\Business\Api\Response\Extractor\SearchResponseExtractorInterface;

class SuggestionsSearchResponseBuilder implements SuggestionsSearchResponseBuilderInterface
{
    /**
     * @var \SprykerEco\Zed\Algolia\Business\Api\Response\Extractor\SearchResponseExtractorInterface
     */
    protected SearchResponseExtractorInterface $completionsExtractor;

    /**
     * @var array<\SprykerEco\Zed\Algolia\Business\Api\Response\Extractor\SearchResponseExtractorInterface>
     */
    protected array $suggestionsExtractors;

    /**
     * @var \SprykerEco\Zed\Algolia\Business\Api\Response\Extractor\SearchResponseExtractorInterface
     */
    protected SearchResponseExtractorInterface $categoryExtractor;

    /**
     * @param \SprykerEco\Zed\Algolia\Business\Api\Response\Extractor\SearchResponseExtractorInterface $completionsExtractor
     * @param array<\SprykerEco\Zed\Algolia\Business\Api\Response\Extractor\SearchResponseExtractorInterface> $suggestionsExtractors
     * @param \SprykerEco\Zed\Algolia\Business\Api\Response\Extractor\SearchResponseExtractorInterface $categoryExtractor
     */
    public function __construct(
        SearchResponseExtractorInterface $completionsExtractor,
        array $suggestionsExtractors,
        SearchResponseExtractorInterface $categoryExtractor
    ) {
        $this->completionsExtractor = $completionsExtractor;
        $this->suggestionsExtractors = $suggestionsExtractors;
        $this->categoryExtractor = $categoryExtractor;
    }

    /**
     * @param \Generated\Shared\Transfer\AlgoliaSearchResponseTransfer $algoliaSearchResponseTransfer
     * @param \Generated\Shared\Transfer\SearchRequestTransfer $searchRequestTransfer
     *
     * @return \Generated\Shared\Transfer\SuggestionsSearchResponseTransfer
     */
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

    /**
     * @param string $errorMessage
     * @param int $statusCode
     *
     * @return \Generated\Shared\Transfer\SuggestionsSearchResponseTransfer
     */
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
