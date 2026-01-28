<?php

/**
 * Copyright © 2022-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerEcoTest\Zed\Algolia\Business\Api\Client;

use Algolia\AlgoliaSearch\SearchIndex;
use Codeception\Test\Unit;
use Generated\Shared\Transfer\AlgoliaResponseTransfer;
use Generated\Shared\Transfer\AlgoliaSearchResponseTransfer;
use PHPUnit\Framework\MockObject\MockObject;
use SprykerEco\Zed\Algolia\Business\Api\Client\SearchIndexClient;
use SprykerEcoTest\Zed\Algolia\AlgoliaBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group PyzTest
 * @group Zed
 * @group Algolia
 * @group Business
 * @group Api
 * @group Client
 * @group SearchIndexClientTest
 * Add your own group annotations below this line
 */
class SearchIndexClientTest extends Unit
{
    /**
     * @var string
     */
    protected const TEST_INDEX_NAME = 'test-index';

    /**
     * @var array<string, mixed>
     */
    protected const TEST_SEARCH_RESULTS = [
        'hits' => [
            ['objectID' => '1', 'name' => 'Test Product 1'],
            ['objectID' => '2', 'name' => 'Test Product 2'],
        ],
        'nbHits' => 2,
    ];

    /**
     * @var array<string, mixed>
     */
    protected const TEST_SETTINGS = [
        'searchableAttributes' => ['name', 'description'],
        'attributesForFaceting' => ['category', 'brand'],
    ];

    /**
     * @var \SprykerEcoTest\Zed\Algolia\AlgoliaBusinessTester
     */
    protected AlgoliaBusinessTester $tester;

    /**
     * @return void
     */
    public function testSaveObjectsSuccessfullyCallsAlgoliaAndReturnsSuccessResponse(): void
    {
        // Arrange
        $algoliaObjectTransfers = [
            ['objectID' => '1', 'name' => 'Test Product 1'],
            ['objectID' => '2', 'name' => 'Test Product 2'],
        ];

        $searchIndexMock = $this->createSearchIndexMock();
        $searchIndexMock
            ->expects($this->once())
            ->method('saveObjects')
            ->with($algoliaObjectTransfers);

        $searchIndexClient = new SearchIndexClient($searchIndexMock);

        // Act
        $result = $searchIndexClient->saveObjects($algoliaObjectTransfers);

        // Assert
        $this->assertInstanceOf(AlgoliaResponseTransfer::class, $result);
        $this->assertTrue($result->getIsSuccessful());
    }

    /**
     * @return void
     */
    public function testDeleteObjectsSuccessfullyCallsAlgoliaAndReturnsSuccessResponse(): void
    {
        // Arrange
        $objectIds = ['1', '2', '3'];

        $searchIndexMock = $this->createSearchIndexMock();
        $searchIndexMock
            ->expects($this->once())
            ->method('deleteObjects')
            ->with($objectIds);

        $searchIndexClient = new SearchIndexClient($searchIndexMock);

        // Act
        $result = $searchIndexClient->deleteObjects($objectIds);

        // Assert
        $this->assertInstanceOf(AlgoliaResponseTransfer::class, $result);
        $this->assertTrue($result->getIsSuccessful());
    }

    /**
     * @return void
     */
    public function testSearchSuccessfullyCallsAlgoliaAndReturnsSearchResponse(): void
    {
        // Arrange
        $query = 'test query';
        $searchParameters = ['filters' => 'category:electronics'];

        $searchIndexMock = $this->createSearchIndexMock();
        $searchIndexMock
            ->expects($this->once())
            ->method('search')
            ->with($query, $searchParameters)
            ->willReturn(static::TEST_SEARCH_RESULTS);

        $searchIndexClient = new SearchIndexClient($searchIndexMock);

        // Act
        $result = $searchIndexClient->search($query, $searchParameters);

        // Assert
        $this->assertInstanceOf(AlgoliaSearchResponseTransfer::class, $result);
        $this->assertTrue($result->getIsSuccessful());
        $this->assertEquals(static::TEST_SEARCH_RESULTS, $result->getSearchResults());
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Algolia\AlgoliaSearch\SearchIndex
     */
    protected function createSearchIndexMock(): MockObject|SearchIndex
    {
        return $this->createMock(SearchIndex::class);
    }
}
