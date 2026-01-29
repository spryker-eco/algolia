<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Zed\Algolia\Business\Api\Response\Extractor;

use Codeception\Test\Unit;
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
 * @group ProductsExtractorTest
 * Add your own group annotations below this line
 */
class ProductsExtractorTest extends Unit
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

    /**
     * @return void
     */
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
}
