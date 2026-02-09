<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Client\Algolia\Api\Response\Extractor;

use Codeception\Test\Unit;
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
 * @group CompletionsExtractorTest
 * Add your own group annotations below this line
 */
class CompletionsExtractorTest extends Unit
{
    /**
     * @var \SprykerEcoTest\Client\Algolia\AlgoliaClientTester
     */
    protected AlgoliaClientTester $tester;

    public function testProductsExtractedWhenHitsExistInResponseData(): void
    {
        // Arrange
        $normalResponseFixtures = $this->tester->loadNormalSuggestionsSearchResponseFixtures();
        $completionsExtractor = $this->tester->getFactory()->createCompletionsExtractor();

        // Act
        $completions = $completionsExtractor->extract($normalResponseFixtures);

        // Assert
        $hits = $normalResponseFixtures->getSearchResults()['completions']['hits'];

        $this->assertCount(3, $hits);
        $this->assertCount(4, $completions);

        foreach ($hits as $index => $hit) {
            $this->assertSame($completions[$index], $hit['query']);
        }
    }

    public function testProductsExtractedWhenHitsDoNotExistInResponseData(): void
    {
        // Arrange
        $emptyResponseFixtures = $this->tester->loadEmptySuggestionsSearchResponseFixtures();
        $completionsExtractor = $this->tester->getFactory()->createCompletionsExtractor();

        // Act
        $completions = $completionsExtractor->extract($emptyResponseFixtures);

        // Assert
        $this->assertCount(0, $completions);
    }
}
