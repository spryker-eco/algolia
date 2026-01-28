<?php

/**
 * Copyright © 2022-present Spryker Systems GmbH. All rights reserved.
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
 * @group CategoryExtractorTest
 * Add your own group annotations below this line
 */
class CategoryExtractorTest extends Unit
{
    /**
     * @var \SprykerEcoTest\Zed\Algolia\AlgoliaBusinessTester
     */
    protected AlgoliaBusinessTester $tester;

    /**
     * @return void
     */
    public function testCategoriesExtractedWhenFacetHitsExistInResponseData(): void
    {
        // Arrange
        $normalResponseFixtures = $this->tester->loadNormalSuggestionsSearchResponseFixtures();
        $categoriesExtractor = $this->tester->getFactory()->createCategoryExtractor();

        // Act
        $categories = $categoriesExtractor->extract($normalResponseFixtures);

        // Assert
        $hits = $normalResponseFixtures->getSearchResults()['completions']['hits'];

        $this->assertCount(3, $hits);
        $this->assertCount(3, $categories);
        $this->assertSame(['Smartphone', 'Smartwatches', 'Fruits'], $categories);
    }

    /**
     * @return void
     */
    public function testProductsExtractedWhenHitsDoNotExistInResponseData(): void
    {
        // Arrange
        $emptyResponseFixtures = $this->tester->loadEmptySuggestionsSearchResponseFixtures();
        $categoriesExtractor = $this->tester->getFactory()->createCategoryExtractor();

        // Act
        $completions = $categoriesExtractor->extract($emptyResponseFixtures);

        // Assert
        $this->assertCount(0, $completions);
    }
}
