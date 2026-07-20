<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Algolia\Api\Response\Extractor;

use ArrayObject;
use Generated\Shared\Transfer\AlgoliaSearchResponseTransfer;
use Generated\Shared\Transfer\SearchRequestTransfer;
use Generated\Shared\Transfer\SearchResponseProductAttributeTransfer;
use Generated\Shared\Transfer\SearchResponseProductPriceTransfer;
use Generated\Shared\Transfer\SearchResponseProductTransfer;
use SprykerEco\Shared\Algolia\Enum\AlgoliaEntityNameEnum;
use SprykerEco\Shared\Algolia\Enum\AlgoliaProductObjectEnum;

class ProductsExtractor implements SearchResponseExtractorInterface
{
    public function getName(): string
    {
        return AlgoliaEntityNameEnum::PRODUCT->value;
    }

    protected const string FIELD_IMAGES = AlgoliaProductObjectEnum::IMAGES->value;

    protected const string FIELD_PRICES = AlgoliaProductObjectEnum::PRICES->value;

    protected const string FIELD_ATTRIBUTES = AlgoliaProductObjectEnum::ATTRIBUTES->value;

    /**
     * @var string
     */
    protected const PRICE_FIELD_NET = 'net';

    /**
     * @var string
     */
    protected const PRICE_FIELD_GROSS = 'gross';

    public function isApplicable(SearchRequestTransfer $searchRequestTransfer): bool
    {
        return $searchRequestTransfer->getSourceIdentifier() === AlgoliaEntityNameEnum::PRODUCT->value;
    }

    public function extract(AlgoliaSearchResponseTransfer $algoliaSearchResponseTransfer): array
    {
        $result = [];

        $hits = $this->getHits($algoliaSearchResponseTransfer->getSearchResults());

        foreach ($hits as $hit) {
            $searchResponseProductTransfer = (new SearchResponseProductTransfer())
            ->fromArray($hit, true)
            ->setImages($hit[static::FIELD_IMAGES])
            ->setPrices($this->extractPrices($hit[static::FIELD_PRICES] ?? []))
            ->setAttributes($this->extractAttributes($hit[static::FIELD_ATTRIBUTES] ?? []));

            $result[] = $searchResponseProductTransfer->toArray();
        }

        return $result;
    }

    /**
     * @param array<mixed> $searchResults
     *
     * @return array<int, mixed>
     */
    protected function getHits(array $searchResults): array
    {
        if (isset($searchResults[static::FIELD_HITS]) && is_array($searchResults[static::FIELD_HITS])) {
            return $searchResults[static::FIELD_HITS];
        }

        return [];
    }

    /**
     * @param array<mixed> $prices
     *
     * @return \ArrayObject<int, \Generated\Shared\Transfer\SearchResponseProductPriceTransfer>
     */
    protected function extractPrices(array $prices): ArrayObject
    {
        $searchResponseProductPriceTransferCollection = new ArrayObject();

        foreach ($prices as $currency => $price) {
            $searchResponseProductPriceTransferCollection->append(
                (new SearchResponseProductPriceTransfer())
                ->setCurrency($currency)
                ->setPriceGross($price[static::PRICE_FIELD_GROSS])
                ->setPriceNet($price[static::PRICE_FIELD_NET]),
            );
        }

        return $searchResponseProductPriceTransferCollection;
    }

    /**
     * @param array<string, string> $attributes
     *
     * @return \ArrayObject<int, \Generated\Shared\Transfer\SearchResponseProductAttributeTransfer>
     */
    protected function extractAttributes(array $attributes): ArrayObject
    {
        $searchResponseProductAttributeTransferCollection = new ArrayObject();

        foreach ($attributes as $key => $value) {
            $searchResponseProductAttributeTransferCollection->offsetSet(
                $key,
                (new SearchResponseProductAttributeTransfer())
                ->setName($key)
                ->setValue($value),
            );
        }

        return $searchResponseProductAttributeTransferCollection;
    }
}
