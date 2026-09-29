<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Client\Algolia\Api\Response\Extractor;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\AlgoliaSearchResponseTransfer;
use SprykerEcoTest\Client\Algolia\AlgoliaClientTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerEcoTest
 * @group Client
 * @group Algolia
 * @group Business
 * @group Api
 * @group Response
 * @group Extractor
 * @group ProductsExtractorTest
 * Add your own group annotations below this line
 */
class ProductsExtractorTest extends Unit
{
    /**
     * @var \SprykerEcoTest\Client\Algolia\AlgoliaClientTester
     */
    protected AlgoliaClientTester $tester;

    public function testProductsExtractedWhenHitsExistInResponseData(): void
    {
        // Arrange
        $normalResponseFixtures = $this->tester->loadNormalSearchResponseFixtures();
        $productsExtractor = $this->tester->getFactory()->createProductsExtractor();

        // Act
        $products = $productsExtractor->extract($normalResponseFixtures);

        // Assert
        $hits = $normalResponseFixtures->getSearchResults()['hits'];

        $this->assertSame(count($hits), count($products));
        foreach ($products as $index => $product) {
            $this->assertSame($hits[$index]['sku'], $product['sku']);
            $this->assertSame($hits[$index]['name'], $product['name']);
            $this->assertSame($hits[$index]['category'], $product['category']);
            $this->assertSame($hits[$index]['images'], $product['images']);
            $this->assertSame($hits[$index]['description'], $product['description']);
            $this->assertSame($hits[$index]['label'], $product['label']);
            $this->assertSame(count($hits[$index]['attributes']), count($product['attributes']));
            $this->assertSame($hits[$index]['keywords'], $product['keywords']);
            $this->assertSame($hits[$index]['url'], $product['url']);
            $this->assertSame($hits[$index]['rating'], $product['rating']);
            $this->assertPrices($hits[$index]['prices'], $product['prices']);
        }
    }

    public function testProductsExtractedWhenHitsDoNotExistInResponseData(): void
    {
        // Arrange
        $emptyResponseFixtures = $this->tester->loadEmptySearchResponseFixtures();
        $productsExtractor = $this->tester->getFactory()->createProductsExtractor();

        // Act
        $products = $productsExtractor->extract($emptyResponseFixtures);

        // Assert
        $this->assertSame(0, count($products));
    }

    /**
     * @param array<mixed> $rawPrices
     * @param array<\Generated\Shared\Transfer\SearchResponseProductPriceTransfer> $prices
     */
    protected function assertPrices(array $rawPrices, array $prices): void
    {
        foreach ($prices as $price) {
            $this->assertSame($rawPrices[$price['currency']]['gross'], $price['price_gross']);
            $this->assertSame($rawPrices[$price['currency']]['net'], $price['price_net']);
        }
    }

    public function testJoinsArrayValuedAttributesByTheirConfiguredDelimiter(): void
    {
        // Arrange
        $this->tester->mockConfigMethod('getMultiValueProductAttributeDelimiters', [
            'color' => ',',
            'size' => '|',
        ]);
        $algoliaSearchResponseTransfer = $this->createSearchResponseTransferWithAttributes([
            'color' => ['red', 'blue'],
            'size' => ['5,5Gb', '6,5Gb'],
            'brand' => 'Acme',
        ]);

        // Act
        $products = $this->tester->getFactory()->createProductsExtractor()->extract($algoliaSearchResponseTransfer);

        // Assert
        $attributes = $products[0]['attributes'];
        $this->assertSame('red,blue', $attributes['color']['value']);
        $this->assertSame('5,5Gb|6,5Gb', $attributes['size']['value']);
        $this->assertSame('Acme', $attributes['brand']['value']);
    }

    public function testJoinsAnArrayValuedAttributeWithoutAConfiguredDelimiterByTheDefaultOne(): void
    {
        // Arrange
        $this->tester->mockConfigMethod('getMultiValueProductAttributeDelimiters', []);
        $algoliaSearchResponseTransfer = $this->createSearchResponseTransferWithAttributes([
            'color' => ['red', 'blue'],
        ]);

        // Act
        $products = $this->tester->getFactory()->createProductsExtractor()->extract($algoliaSearchResponseTransfer);

        // Assert
        $this->assertSame('red, blue', $products[0]['attributes']['color']['value']);
    }

    public function testReadsTheDelimitersFromTheSharedConfiguration(): void
    {
        // Arrange
        $this->tester->mockSharedConfigMethod('getMultiValueProductAttributeDelimiters', ['color' => '|']);
        $algoliaSearchResponseTransfer = $this->createSearchResponseTransferWithAttributes([
            'color' => ['red', 'blue'],
        ]);

        // Act
        $products = $this->tester->getFactory()->createProductsExtractor()->extract($algoliaSearchResponseTransfer);

        // Assert
        $this->assertSame('red|blue', $products[0]['attributes']['color']['value']);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    protected function createSearchResponseTransferWithAttributes(array $attributes): AlgoliaSearchResponseTransfer
    {
        return (new AlgoliaSearchResponseTransfer())->setSearchResults([
            'hits' => [
                [
                    'sku' => 'multi-value-sku',
                    'name' => 'multi-value product',
                    'images' => [],
                    'attributes' => $attributes,
                ],
            ],
        ]);
    }
}
