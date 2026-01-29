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
 * @group PaginationExtractorTest
 * Add your own group annotations below this line
 */
class PaginationExtractorTest extends Unit
{
    /**
     * @var \SprykerEcoTest\Zed\Algolia\AlgoliaBusinessTester
     */
    protected AlgoliaBusinessTester $tester;

    /**
     * @return void
     */
    public function testPaginationExtractedWhenItExistsInResponseData(): void
    {
        // Arrange
        $normalResponseFixtures = $this->tester->loadNormalSearchResponseFixtures();
        $searchRequestTransfer = $this->tester->haveSearchRequestTransfer();
        $paginationExtractor = $this->tester->getFactory()->createPaginationExtractor();

        // Act
        $searchResponsePaginationTransfer = $paginationExtractor->extract($normalResponseFixtures, $searchRequestTransfer);

        // Assert
        $this->assertSame(($normalResponseFixtures->getSearchResults()['page'] + 1), $searchResponsePaginationTransfer->getCurrentPage());
        $this->assertSame($normalResponseFixtures->getSearchResults()['hitsPerPage'], $searchResponsePaginationTransfer->getCurrentItemsPerPage());
        $this->assertSame($normalResponseFixtures->getSearchResults()['nbHits'], $searchResponsePaginationTransfer->getNumFound());
    }

    /**
     * @return void
     */
    public function testPaginationExtractedWhenItDoesNotExistInResponseData(): void
    {
        // Arrange
        $emptyResponseFixtures = $this->tester->loadEmptySearchResponseFixtures();
        $searchRequestTransfer = $this->tester->haveSearchRequestTransfer();
        $paginationExtractor = $this->tester->getFactory()->createPaginationExtractor();

        // Act
        $searchResponsePaginationTransfer = $paginationExtractor->extract($emptyResponseFixtures, $searchRequestTransfer);

        // Assert
        $this->assertSame(1, $searchResponsePaginationTransfer->getCurrentPage());
        $this->assertSame(12, $searchResponsePaginationTransfer->getCurrentItemsPerPage());
        $this->assertSame(0, $searchResponsePaginationTransfer->getNumFound());
    }
}
