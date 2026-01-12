<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Api\Response\Builder;

use Generated\Shared\Transfer\AlgoliaSearchResponseTransfer;
use Generated\Shared\Transfer\GlueErrorTransfer;
use Generated\Shared\Transfer\SearchRequestTransfer;
use Generated\Shared\Transfer\SearchResponseTransfer;
use SprykerEco\Zed\Algolia\Business\Api\Response\Extractor\FacetsExtractorInterface;
use SprykerEco\Zed\Algolia\Business\Api\Response\Extractor\PaginationExtractorInterface;
use SprykerEco\Zed\Algolia\Business\Api\Response\Extractor\SearchResponseExtractorInterface;

class SearchResponseBuilder implements SearchResponseBuilderInterface
{
    /**
     * @var array<\SprykerEco\Zed\Algolia\Business\Api\Response\Extractor\SearchResponseExtractorInterface>
     */
    protected array $sourceIdentifierExtractors;

    /**
     * @var \SprykerEco\Zed\Algolia\Business\Api\Response\Extractor\PaginationExtractorInterface
     */
    protected PaginationExtractorInterface $paginationExtractor;

    /**
     * @var \SprykerEco\Zed\Algolia\Business\Api\Response\Extractor\FacetsExtractorInterface
     */
    protected FacetsExtractorInterface $facetsExtractor;

    /**
     * @param array<\SprykerEco\Zed\Algolia\Business\Api\Response\Extractor\SearchResponseExtractorInterface> $sourceIdentifierExtractors
     */
    public function __construct(
        array $sourceIdentifierExtractors,
        PaginationExtractorInterface $paginationExtractor,
        FacetsExtractorInterface $facetsExtractor
    ) {
        $this->sourceIdentifierExtractors = $sourceIdentifierExtractors;
        $this->paginationExtractor = $paginationExtractor;
        $this->facetsExtractor = $facetsExtractor;
    }

    public function buildSuccessfulResponse(
        AlgoliaSearchResponseTransfer $algoliaSearchResponseTransfer,
        SearchRequestTransfer $searchRequestTransfer
    ): SearchResponseTransfer {
        return (new SearchResponseTransfer())
            ->setQueryId($algoliaSearchResponseTransfer->getSearchResults()['queryID'] ?? null)
            ->setIsSuccessful(true)
            ->setItems($this->getSourceIdentifierItems($algoliaSearchResponseTransfer, $searchRequestTransfer))
            ->setPagination($this->paginationExtractor->extract($algoliaSearchResponseTransfer, $searchRequestTransfer))
            ->setFacets($this->facetsExtractor->extract($algoliaSearchResponseTransfer, $searchRequestTransfer));
    }

    public function buildUnsuccessfulResponse(string $errorMessage, int $statusCode): SearchResponseTransfer
    {
        return (new SearchResponseTransfer())
            ->setIsSuccessful(false)
            ->setStatusCode($statusCode)
            ->addError(
                (new GlueErrorTransfer())
                    ->setStatus($statusCode)
                    ->setMessage($errorMessage),
            );
    }

    /**
     * @return array
     */
    protected function getSourceIdentifierItems(
        AlgoliaSearchResponseTransfer $algoliaSearchResponseTransfer,
        SearchRequestTransfer $searchRequestTransfer
    ): array {
        foreach ($this->sourceIdentifierExtractors as $sourceIdentifierExtractor) {
            if (!$sourceIdentifierExtractor->isApplicable($searchRequestTransfer)) {
                continue;
            }

            return $sourceIdentifierExtractor->extract($algoliaSearchResponseTransfer);
        }

        // default extractor
        return $algoliaSearchResponseTransfer->getSearchResults()[SearchResponseExtractorInterface::FIELD_HITS] ?? [];
    }
}
