<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Algolia\Api\Response\Extractor;

use Generated\Shared\Transfer\AlgoliaSearchResponseTransfer;
use Generated\Shared\Transfer\SearchResponseProductTransfer;
use Generated\Shared\Transfer\SuggestionsMatchesCollectionTransfer;
use SprykerEco\Client\Algolia\AlgoliaConfig;

class SuggestionsProductsExtractor extends ProductsExtractor implements SearchResponseExtractorInterface
{
    /**
     * @var string
     */
    protected const FIELD_SUGGESTIONS = 'suggestions';

    /**
     * @var \SprykerEco\Client\Algolia\AlgoliaConfig
     */
    protected AlgoliaConfig $algoliaConfig;

    public function __construct(AlgoliaConfig $algoliaConfig)
    {
        $this->algoliaConfig = $algoliaConfig;
    }

    /**
     * @return array<string, mixed>
     */
    public function extract(AlgoliaSearchResponseTransfer $algoliaSearchResponseTransfer): array
    {
        $suggestionsMatchesCollectionTransfer = new SuggestionsMatchesCollectionTransfer();
        $matches = [];
        $categories = [];
        $matchedItems = [];
        $hits = $this->getHits($algoliaSearchResponseTransfer->getSearchResults()[static::FIELD_SUGGESTIONS]);
        foreach ($hits as $hit) {
            $searchResponseProductTransfer = (new SearchResponseProductTransfer())
                ->fromArray($hit, true)
                ->setImages($hit[static::FIELD_IMAGES])
                ->setPrices($this->extractPrices($hit[static::FIELD_PRICES] ?? []))
                ->setAttributes($this->extractAttributes($hit[static::FIELD_ATTRIBUTES] ?? []));

            foreach ($hit['_highlightResult'] as $field => $data) {
                if (!in_array($field, $this->algoliaConfig->getAttributesToHighlight())) {
                    continue;
                }

                if (!isset($data[0])) {
                    if ($data['matchLevel'] === 'none') {
                        continue;
                    }
                    $matches[$field][] = $hit['sku'];
                    $matchedItems[$hit['sku']] = $searchResponseProductTransfer->toArray();
                } else {
                    foreach ($data as $key => $datum) {
                        if ($datum['matchLevel'] === 'none') {
                            continue;
                        }
                        if ($field === AlgoliaConfig::INDEXED_PRODUCT_FIELD_NAME_CATEGORY) {
                            $categories[$hit['category'][$key]] = $hit['category'][$key];
                        }
                        $matches[$field][$hit['sku']] = $hit['sku'];
                        $matchedItems[$hit['sku']] = $searchResponseProductTransfer->toArray();
                    }
                    if (array_key_exists($field, $matches)) {
                        $matches[$field] = array_values($matches[$field]);
                    }
                }
            }
        }

        $suggestionsMatchesCollectionTransfer->setMatches($matches)
            ->setMatchedItems(array_values($matchedItems))
            ->setCategories(array_values($categories));

        return $suggestionsMatchesCollectionTransfer->toArray();
    }
}
