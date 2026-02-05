<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Zed\Algolia\Business\Api\IndexReader;

use Codeception\Test\Unit;
use Exception;
use SprykerEco\Zed\Algolia\Business\Api\IndexReader\IndexReader;

/**
 * Auto-generated group annotations
 *
 * @group SprykerEcoTest
 * @group Zed
 * @group Algolia
 * @group Business
 * @group Api
 * @group IndexReader
 * @group IndexReaderTest
 * Add your own group annotations below this line
 */
class IndexReaderTest extends Unit
{
    /**
     * @var \SprykerEcoTest\Zed\Algolia\AlgoliaBusinessTester
     */
    protected $tester;

    /**
     * @return void
     */
    public function testGetIndicesReturnsListOfIndices(): void
    {
        // Arrange
        $searchClient = $this->tester->createSearchClientMock();
        $searchClient = $this->tester->searchClientMockReturnsTwoIndices($searchClient);
        $indexReader = new IndexReader($this->tester->getFactory()->createIndexMapper());

        // Act
        $algoliaIndicesCollectionTransfer = $indexReader->getIndices($searchClient);

        // Assert
        $this->assertNotNull($algoliaIndicesCollectionTransfer);

        $this->assertCount(2, $algoliaIndicesCollectionTransfer->getIndices());
    }

    /**
     * @return void
     */
    public function testGetIndicesReturnsNoIndices(): void
    {
        // Arrange
        $searchClient = $this->tester->createSearchClientMock();
        $searchClient = $this->tester->searchClientMockReturnsZeroIndices($searchClient);
        $indexReader = new IndexReader($this->tester->getFactory()->createIndexMapper());

        // Act
        $algoliaIndicesCollectionTransfer = $indexReader->getIndices($searchClient);

        // Assert
        $this->assertNotNull($algoliaIndicesCollectionTransfer);

        $this->assertCount(0, $algoliaIndicesCollectionTransfer->getIndices());
    }

    /**
     * @return void
     */
    public function testGetIndicesHandlesExceptionGracefully(): void
    {
        // Arrange
        $searchClient = $this->tester->createSearchClientMock();
        $searchClient->method('listIndices')
            ->willThrowException(new Exception('API Error'));

        $indexReader = new IndexReader($this->tester->getFactory()->createIndexMapper());

        // Act
        $algoliaIndicesCollectionTransfer = $indexReader->getIndices($searchClient);

        // Assert
        $this->assertNotNull($algoliaIndicesCollectionTransfer);
        $this->assertCount(0, $algoliaIndicesCollectionTransfer->getIndices());
    }
}
