<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Client\Algolia\Api\Response\Builder;

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
 * @group Builder
 * @group SuggestionsSearchResponseBuilderTest
 * Add your own group annotations below this line
 */
class SuggestionsSearchResponseBuilderTest extends Unit
{
    /**
     * @var \SprykerEcoTest\Client\Algolia\AlgoliaClientTester
     */
    protected AlgoliaClientTester $tester;

    /**
     * @return void
     */
    public function testResponseBuiltSuccessfullyWhenResponseIsNormal(): void
    {
        // Arrange
        $normalResponseFixtures = $this->tester->loadNormalSuggestionsSearchResponseFixtures();
        $searchRequestTransfer = $this->tester->haveSearchRequestTransfer();
        $suggestionsSuggestionsSearchResponseBuilder = $this->tester->getFactory()->createSuggestionsSearchResponseBuilder();

        // Act
        $suggestionsSearchResponseTransfer = $suggestionsSuggestionsSearchResponseBuilder->buildSuccessfulResponse($normalResponseFixtures, $searchRequestTransfer);

        // Assert
        $this->assertTrue($suggestionsSearchResponseTransfer->getIsSuccessful());
        $this->assertEmpty($suggestionsSearchResponseTransfer->getErrors());
        $this->assertSame(3, count($suggestionsSearchResponseTransfer->getMatches()));

        $matchesCategory = $suggestionsSearchResponseTransfer->getMatches()['category'];
        $this->assertSame(array_unique($matchesCategory), $matchesCategory);
        $this->assertSame(16, count($suggestionsSearchResponseTransfer->getMatchedItems()));
        $this->assertSame(4, count($suggestionsSearchResponseTransfer->getCompletions()));
        $this->assertSame(5, count($suggestionsSearchResponseTransfer->getCategories()));
        $this->assertArrayHasKey('name', $suggestionsSearchResponseTransfer->getMatches());
        $this->assertArrayHasKey('abstract_name', $suggestionsSearchResponseTransfer->getMatches());

        $skus = array_column($suggestionsSearchResponseTransfer->getMatchedItems(), 'sku');
        foreach ($suggestionsSearchResponseTransfer->getMatches() as $match) {
            $this->assertEmpty(array_diff($match, $skus));
        }
    }

    /**
     * @return void
     */
    public function testResponseBuiltSuccessfullyWhenResponseMatchesIsEmpty(): void
    {
        // Arrange
        $emptyResponseFixtures = $this->tester->loadEmptyMatchSuggestionsSearchResponseFixtures();
        $searchRequestTransfer = $this->tester->haveSearchRequestTransfer();
        $suggestionsSuggestionsSearchResponseBuilder = $this->tester->getFactory()->createSuggestionsSearchResponseBuilder();

        // Act
        $suggestionsSearchResponseTransfer = $suggestionsSuggestionsSearchResponseBuilder->buildSuccessfulResponse($emptyResponseFixtures, $searchRequestTransfer);

        // Assert
        $this->assertTrue($suggestionsSearchResponseTransfer->getIsSuccessful());
        $this->assertEmpty($suggestionsSearchResponseTransfer->getErrors());
        $this->assertEmpty($suggestionsSearchResponseTransfer->getMatches());
        $this->assertEmpty($suggestionsSearchResponseTransfer->getMatchedItems());
        $this->assertEmpty($suggestionsSearchResponseTransfer->getCategories());
        $this->assertSame(20, count($suggestionsSearchResponseTransfer->getCompletions()));
    }

    /**
     * @return void
     */
    public function testResponseBuiltSuccessfullyWhenResponseIsEmpty(): void
    {
        // Arrange
        $emptyResponseFixtures = $this->tester->loadEmptySuggestionsSearchResponseFixtures();
        $searchRequestTransfer = $this->tester->haveSearchRequestTransfer();
        $suggestionsSuggestionsSearchResponseBuilder = $this->tester->getFactory()->createSuggestionsSearchResponseBuilder();

        // Act
        $suggestionsSearchResponseTransfer = $suggestionsSuggestionsSearchResponseBuilder->buildSuccessfulResponse($emptyResponseFixtures, $searchRequestTransfer);

        // Assert
        $this->assertTrue($suggestionsSearchResponseTransfer->getIsSuccessful());
        $this->assertEmpty($suggestionsSearchResponseTransfer->getErrors());
        $this->assertEmpty($suggestionsSearchResponseTransfer->getCompletions());
        $this->assertEmpty($suggestionsSearchResponseTransfer->getCategories());
        $this->assertEmpty($suggestionsSearchResponseTransfer->getMatches());
        $this->assertEmpty($suggestionsSearchResponseTransfer->getMatchedItems());
    }

    /**
     * @return void
     */
    public function testResponseHasBuiltUnsuccessfulResponseOnUnexpectedException(): void
    {
        // Arrange
        $errorMessage = 'Unexpected error occurred';
        $errorCode = 500;
        $suggestionsSuggestionsSearchResponseBuilder = $this->tester->getFactory()->createSuggestionsSearchResponseBuilder();

        // Act
        $suggestionsSearchResponseTransfer = $suggestionsSuggestionsSearchResponseBuilder->buildUnsuccessfulResponse($errorMessage, $errorCode);

        // Assert
        $this->assertFalse($suggestionsSearchResponseTransfer->getIsSuccessful());
        $this->assertSame($errorMessage, $suggestionsSearchResponseTransfer->getErrors()->offsetGet(0)->getMessage());
        $this->assertSame($errorCode, $suggestionsSearchResponseTransfer->getStatusCode());
        $this->assertEmpty($suggestionsSearchResponseTransfer->getCompletions());
        $this->assertEmpty($suggestionsSearchResponseTransfer->getCategories());
        $this->assertEmpty($suggestionsSearchResponseTransfer->getMatches());
        $this->assertEmpty($suggestionsSearchResponseTransfer->getMatchedItems());
    }
}
