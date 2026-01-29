<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Zed\Algolia\Business\Api\Response\Builder;

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
 * @group Builder
 * @group SearchResponseBuilderTest
 * Add your own group annotations below this line
 */
class SearchResponseBuilderTest extends Unit
{
    /**
     * @var \SprykerEcoTest\Zed\Algolia\AlgoliaBusinessTester
     */
    protected AlgoliaBusinessTester $tester;

    /**
     * @return void
     */
    public function testResponseBuiltSuccessfullyWhenResponseIsNormal(): void
    {
        // Arrange
        $normalResponseFixtures = $this->tester->loadNormalSearchResponseFixtures();
        $searchRequestTransfer = $this->tester->haveSearchRequestTransfer();
        $searchResponseBuilder = $this->tester->getFactory()->createSearchResponseBuilder();

        // Act
        $searchResponseTransfer = $searchResponseBuilder->buildSuccessfulResponse($normalResponseFixtures, $searchRequestTransfer);

        // Assert
        $this->assertTrue($searchResponseTransfer->getIsSuccessful());
        $this->assertEmpty($searchResponseTransfer->getErrors());
        $this->assertNotEmpty($searchResponseTransfer->getPagination());
        $this->assertSame(5, count($searchResponseTransfer->getItems()));
        $this->assertSame(3, count($searchResponseTransfer->getFacets()));
    }

    /**
     * @return void
     */
    public function testResponseBuiltSuccessfullyWhenResponseIsEmpty(): void
    {
        // Arrange
        $emptyResponseFixtures = $this->tester->loadEmptySearchResponseFixtures();
        $searchRequestTransfer = $this->tester->haveSearchRequestTransfer();
        $searchResponseBuilder = $this->tester->getFactory()->createSearchResponseBuilder();

        // Act
        $searchResponseTransfer = $searchResponseBuilder->buildSuccessfulResponse($emptyResponseFixtures, $searchRequestTransfer);

        // Assert
        $this->assertTrue($searchResponseTransfer->getIsSuccessful());
        $this->assertEmpty($searchResponseTransfer->getErrors());
        $this->assertNotEmpty($searchResponseTransfer->getPagination());
        $this->assertSame(0, count($searchResponseTransfer->getItems()));
        $this->assertSame(0, count($searchResponseTransfer->getFacets()));
    }

    /**
     * @return void
     */
    public function testResponseHasBuiltUnsuccessfulResponseOnUnexpectedException(): void
    {
        // Arrange
        $errorMessage = 'Unexpected error occurred';
        $errorCode = 500;
        $searchResponseBuilder = $this->tester->getFactory()->createSearchResponseBuilder();

        // Act
        $searchResponseTransfer = $searchResponseBuilder->buildUnsuccessfulResponse($errorMessage, $errorCode);

        // Assert
        $this->assertFalse($searchResponseTransfer->getIsSuccessful());
        $this->assertSame($errorMessage, $searchResponseTransfer->getErrors()->offsetGet(0)->getMessage());
        $this->assertSame($errorCode, $searchResponseTransfer->getStatusCode());
        $this->assertEmpty($searchResponseTransfer->getItems());
        $this->assertEmpty($searchResponseTransfer->getPagination());
        $this->assertEmpty($searchResponseTransfer->getFacets());
    }
}
