<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Client\Algolia\Helper;

use ArrayObject;
use Codeception\Module;
use Generated\Shared\DataBuilder\FacetCollectionBuilder;
use Generated\Shared\DataBuilder\FacetEntryBuilder;
use Generated\Shared\DataBuilder\FacetParametersBuilder;
use Generated\Shared\DataBuilder\PaginationEntryBuilder;
use Generated\Shared\DataBuilder\SearchRequestBuilder;
use Generated\Shared\Transfer\FacetCollectionTransfer;
use Generated\Shared\Transfer\FacetEntryTransfer;
use Generated\Shared\Transfer\FacetParametersTransfer;
use Generated\Shared\Transfer\SearchRequestTransfer;
use SprykerEco\Shared\Algolia\Enum\AlgoliaEntityNameEnum;

class AlgoliaHelper extends Module
{
    /**
     * @param array<mixed> $facets
     */
    public function haveFacetCollectionTransfer(array $facets = []): FacetCollectionTransfer
    {
        return (new FacetCollectionBuilder())
            ->build()
            ->setFacets(new ArrayObject($facets));
    }

    public function haveFacetEntryTransfer(string $type, FacetParametersTransfer $facetParametersTransfer): FacetEntryTransfer
    {
        return (new FacetEntryBuilder())
            ->build()
            ->setType($type)
            ->setParameters($facetParametersTransfer);
    }

    /**
     * @param array $seed
     */
    public function haveFacetParametersTransfer(array $seed = []): FacetParametersTransfer
    {
        return (new FacetParametersBuilder($seed))
            ->build();
    }

    /**
     * @param array<mixed> $seed
     */
    public function haveSearchRequestTransfer(array $seed = []): SearchRequestTransfer
    {
        $paginationSeed = $seed['pagination_entry'] ?? [];
        $paginationEntryTransfer = (new PaginationEntryBuilder($paginationSeed))->build();
        $searchRequestTransfer = (new SearchRequestBuilder($seed))->build();

        if ($searchRequestTransfer->getStoreName() === null) {
            $searchRequestTransfer->setStoreName('DE');
        }
        if ($searchRequestTransfer->getLocale() === null) {
            $searchRequestTransfer->setLocale('en_US');
        }

        if ($searchRequestTransfer->getSourceIdentifier() === null) {
            $searchRequestTransfer->setSourceIdentifier(AlgoliaEntityNameEnum::PRODUCT->value);
        }

        return $searchRequestTransfer
            ->setPagination($paginationEntryTransfer)
            ->setSort();
    }
}
