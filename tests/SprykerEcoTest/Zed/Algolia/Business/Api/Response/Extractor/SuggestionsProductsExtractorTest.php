<?php

/**
 * Copyright © 2022-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Zed\Algolia\Business\Api\Response\Extractor;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\AlgoliaSearchResponseTransfer;
use Generated\Shared\Transfer\SuggestionsMatchesCollectionTransfer;
use SprykerEco\Zed\Algolia\AlgoliaConfig;
use SprykerEco\Zed\Algolia\Business\Api\Response\Extractor\SuggestionsProductsExtractor;
use SprykerEcoTest\Zed\Algolia\AlgoliaBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group PyzTest
 * @group Zed
 * @group Algolia
 * @group Business
 * @group Api
 * @group Response
 * @group Extractor
 * @group SuggestionsProductsExtractorTest
 * Add your own group annotations below this line
 */
class SuggestionsProductsExtractorTest extends Unit
{
    /**
     * @var \SprykerEcoTest\Zed\Algolia\AlgoliaBusinessTester
     */
    protected AlgoliaBusinessTester $tester;

    /**
     * @return void
     */
    public function testProductsExtractedWhenHitsExistInResponseData(): void
    {
        // Arrange
        $normalResponseFixtures = $this->tester->loadNormalSuggestionsSearchResponseFixtures();
        $suggestionsProductsExtractor = $this->tester->getFactory()->createSuggestionProductsExtractor();

        // Act
        $data = $suggestionsProductsExtractor->extract($normalResponseFixtures);
        $suggestionsMatchesCollectionTransfer = (new SuggestionsMatchesCollectionTransfer())->fromArray($data, true);

        // Assert
        $hits = $normalResponseFixtures->getSearchResults()['suggestions']['hits'];

        $this->assertCount(20, $hits);
        $this->assertCount(3, $suggestionsMatchesCollectionTransfer->getMatches());

        $matchesCategory = $suggestionsMatchesCollectionTransfer->getMatches()[AlgoliaConfig::INDEXED_PRODUCT_FIELD_NAME_CATEGORY];
        $this->assertSame(array_unique($matchesCategory), $matchesCategory);
        $this->assertCount(16, $suggestionsMatchesCollectionTransfer->getMatchedItems());
        $this->assertCount(3, $suggestionsMatchesCollectionTransfer->getCategories());

        $skuToMatchedItems = [];
        foreach ($suggestionsMatchesCollectionTransfer->getMatchedItems() as $matchedItem) {
            $skuToMatchedItems[$matchedItem['sku']] = $matchedItem;
        }

        foreach ($suggestionsMatchesCollectionTransfer->getMatches() as $highlightFiled => $product) {
            $this->assertContains($highlightFiled, $this->tester->getFactory()->getConfig()->getAttributesToHighlight());
        }

        $hitNameToSkus = [];
        foreach ($hits as $hit) {
            foreach ($hit['_highlightResult'] as $field => $matches) {
                if (!in_array($field, $this->tester->getFactory()->getConfig()->getAttributesToHighlight())) {
                    $this->assertProductExistsInResultHit($skuToMatchedItems[$hit['sku']], $hit);

                    continue;
                }

                if (!isset($matches[0])) {
                    if ($matches['matchLevel'] === 'none') {
                        continue;
                    }
                    $this->assertArrayHasKey($field, $suggestionsMatchesCollectionTransfer->getMatches());
                    $this->assertProductExistsInResultHit($skuToMatchedItems[$hit['sku']], $hit);
                    $hitNameToSkus[$field][] = $hit['sku'];
                } else {
                    foreach ($matches as $match) {
                        if ($match['matchLevel'] === 'none') {
                            continue;
                        }
                        $this->assertProductExistsInResultHit($skuToMatchedItems[$hit['sku']], $hit);
                        $hitNameToSkus[$field][$hit['sku']] = $hit['sku'];
                    }
                    $hitNameToSkus[$field] = array_values($hitNameToSkus[$field]);
                }
            }
        }

        foreach ($hitNameToSkus as $name => $skus) {
            if (!$skus) {
                $this->assertArrayNotHasKey($name, $suggestionsMatchesCollectionTransfer->getMatches());

                continue;
            }
            $this->assertSame(count($skus), count($suggestionsMatchesCollectionTransfer->getMatches()[$name]));
        }
    }

    /**
     * @return void
     */
    public function testProductsExtractedWhenHitsDoNotExistInResponseData(): void
    {
        // Arrange
        $emptyResponseFixtures = $this->tester->loadEmptyMatchSuggestionsSearchResponseFixtures();
        $suggestionsProductsExtractor = $this->tester->getFactory()->createSuggestionProductsExtractor();

        // Act
        $data = $suggestionsProductsExtractor->extract($emptyResponseFixtures);
        $suggestionsMatchesCollectionTransfer = (new SuggestionsMatchesCollectionTransfer())->fromArray($data, true);

        // Assert
        $this->assertCount(0, $suggestionsMatchesCollectionTransfer->getMatches());
        $this->assertCount(0, $suggestionsMatchesCollectionTransfer->getMatchedItems());
    }

    /**
     * @param array<string, mixed> $product
     * @param array<string, mixed> $hit
     *
     * @return void
     */
    protected function assertProductExistsInResultHit(array $product, array $hit): void
    {
        $this->assertSame($hit['sku'], $product['sku']);
        $this->assertSame($hit['name'], $product['name']);
        $this->assertSame($hit['category'], $product['category']);
        $this->assertSame($hit['images'], $product['images']);
        $this->assertSame($hit['description'], $product['description']);
        $this->assertSame($hit['label'], $product['label']);
        $this->assertSame(count($hit['attributes']), count($product['attributes']));
        $this->assertSame($hit['keywords'], $product['keywords']);
        $this->assertSame($hit['url'], $product['url']);
        $this->assertSame($hit['rating'], $product['rating']);
        $this->assertPrices($hit['prices'], $product['prices']);
    }

    /**
     * @param array<string, mixed> $rawPrices
     * @param array<\Generated\Shared\Transfer\SearchResponseProductPriceTransfer> $prices
     *
     * @return void
     */
    protected function assertPrices(array $rawPrices, array $prices): void
    {
        foreach ($prices as $price) {
            $this->assertSame($rawPrices[$price['currency']]['gross'], $price['price_gross']);
            $this->assertSame($rawPrices[$price['currency']]['net'], $price['price_net']);
        }
    }

    /**
     * @return void
     */
    public function testExtractHandlesEmptyHighlightResults(): void
    {
        // Arrange
        $searchResults = [
            'suggestions' => [
                'hits' => [
                    [
                        'sku' => 'test-sku-1',
                        'name' => 'Test Product',
                        'category' => 'Test Category', // Added missing category field
                        'images' => [],
                        'prices' => [],
                        'attributes' => [],
                        '_highlightResult' => [], // Empty highlight results
                    ],
                ],
            ],
        ];

        $algoliaSearchResponseTransfer = (new AlgoliaSearchResponseTransfer())
            ->setSearchResults($searchResults);

        $algoliaConfig = $this->createMock(AlgoliaConfig::class);
        $algoliaConfig->method('getAttributesToHighlight')->willReturn(['name', 'category']);

        $suggestionsProductsExtractor = new SuggestionsProductsExtractor($algoliaConfig);

        // Act
        $result = $suggestionsProductsExtractor->extract($algoliaSearchResponseTransfer);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('matches', $result);
        $this->assertEmpty($result['matches']);
    }

    /**
     * @return void
     */
    public function testExtractHandlesCategoryHighlights(): void
    {
        // Arrange
        $searchResults = [
            'suggestions' => [
                'hits' => [
                    [
                        'sku' => 'test-sku-1',
                        'name' => 'Test Product',
                        'category' => ['Electronics', 'Computers'],
                        'images' => [],
                        'prices' => [],
                        'attributes' => [],
                        '_highlightResult' => [
                            'category' => [
                                ['matchLevel' => 'full'],
                                ['matchLevel' => 'partial'],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $algoliaSearchResponseTransfer = (new AlgoliaSearchResponseTransfer())
            ->setSearchResults($searchResults);

        $algoliaConfig = $this->createMock(AlgoliaConfig::class);
        $algoliaConfig->method('getAttributesToHighlight')->willReturn(['category']);

        $suggestionsProductsExtractor = new SuggestionsProductsExtractor($algoliaConfig);

        // Act
        $result = $suggestionsProductsExtractor->extract($algoliaSearchResponseTransfer);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('categories', $result);
        $this->assertContains('Electronics', $result['categories']);
        $this->assertContains('Computers', $result['categories']);
    }

    /**
     * @return void
     */
    public function testExtractSkipsNonHighlightedFields(): void
    {
        // Arrange
        $searchResults = [
            'suggestions' => [
                'hits' => [
                    [
                        'sku' => 'test-sku-1',
                        'name' => 'Test Product',
                        'images' => [],
                        'prices' => [],
                        'attributes' => [],
                        '_highlightResult' => [
                            'name' => ['matchLevel' => 'full'],
                            'description' => ['matchLevel' => 'partial'], // Not in attributesToHighlight
                        ],
                    ],
                ],
            ],
        ];

        $algoliaSearchResponseTransfer = (new AlgoliaSearchResponseTransfer())
            ->setSearchResults($searchResults);

        $algoliaConfig = $this->createMock(AlgoliaConfig::class);
        $algoliaConfig->method('getAttributesToHighlight')->willReturn(['name']); // Only name is highlighted

        $suggestionsProductsExtractor = new SuggestionsProductsExtractor($algoliaConfig);

        // Act
        $result = $suggestionsProductsExtractor->extract($algoliaSearchResponseTransfer);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('matches', $result);
        $this->assertArrayHasKey('name', $result['matches']);
        $this->assertArrayNotHasKey('description', $result['matches']); // Should be skipped
    }

    /**
     * @return void
     */
    public function testExtractSkipsNoneMatchLevel(): void
    {
        // Arrange
        $searchResults = [
            'suggestions' => [
                'hits' => [
                    [
                        'sku' => 'test-sku-1',
                        'name' => 'Test Product',
                        'category' => ['Electronics', 'Computers'], // Added missing category field
                        'images' => [],
                        'prices' => [],
                        'attributes' => [],
                        '_highlightResult' => [
                            'name' => ['matchLevel' => 'none'], // Should be skipped
                            'category' => [
                                ['matchLevel' => 'none'], // Should be skipped
                                ['matchLevel' => 'full'], // Should be included
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $algoliaSearchResponseTransfer = (new AlgoliaSearchResponseTransfer())
            ->setSearchResults($searchResults);

        $algoliaConfig = $this->createMock(AlgoliaConfig::class);
        $algoliaConfig->method('getAttributesToHighlight')->willReturn(['name', 'category']);

        $suggestionsProductsExtractor = new SuggestionsProductsExtractor($algoliaConfig);

        // Act
        $result = $suggestionsProductsExtractor->extract($algoliaSearchResponseTransfer);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('matches', $result);
        $this->assertArrayNotHasKey('name', $result['matches']); // Should be skipped due to 'none' match level
        $this->assertArrayHasKey('category', $result['matches']); // Should be included due to 'full' match level
    }
}
