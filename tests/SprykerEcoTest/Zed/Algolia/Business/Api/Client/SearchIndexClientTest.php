<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerEcoTest\Zed\Algolia\Business\Api\Client;

use Algolia\AlgoliaSearch\Api\SearchClient;
use Codeception\Test\Unit;
use Generated\Shared\Transfer\AlgoliaResponseTransfer;
use Generated\Shared\Transfer\AlgoliaSearchResponseTransfer;
use PHPUnit\Framework\MockObject\MockObject;
use SprykerEco\Zed\Algolia\Business\Api\Client\SearchIndexClient;
use SprykerEcoTest\Zed\Algolia\AlgoliaBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerEcoTest
 * @group Zed
 * @group Algolia
 * @group Business
 * @group Api
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

    protected AlgoliaBusinessTester $tester;

    public function testSaveObjectsSuccessfullyCallsAlgoliaAndReturnsSuccessResponse(): void
    {
        // Arrange
        $algoliaObjectTransfers = [
            ['objectID' => '1', 'name' => 'Test Product 1'],
            ['objectID' => '2', 'name' => 'Test Product 2'],
        ];

        $searchClientMock = $this->createSearchClientMock();
        $searchClientMock
            ->expects($this->once())
            ->method('saveObjects')
            ->with(static::TEST_INDEX_NAME, $algoliaObjectTransfers);

        $searchIndexClient = new SearchIndexClient($searchClientMock, static::TEST_INDEX_NAME);

        // Act
        $result = $searchIndexClient->saveObjects($algoliaObjectTransfers);

        // Assert
        $this->assertInstanceOf(AlgoliaResponseTransfer::class, $result);
        $this->assertTrue($result->getIsSuccessful());
    }

    public function testDeleteObjectsSuccessfullyCallsAlgoliaAndReturnsSuccessResponse(): void
    {
        // Arrange
        $objectIds = ['1', '2', '3'];

        $searchClientMock = $this->createSearchClientMock();
        $searchClientMock
            ->expects($this->once())
            ->method('deleteObjects')
            ->with(static::TEST_INDEX_NAME, $objectIds);

        $searchIndexClient = new SearchIndexClient($searchClientMock, static::TEST_INDEX_NAME);

        // Act
        $result = $searchIndexClient->deleteObjects($objectIds);

        // Assert
        $this->assertInstanceOf(AlgoliaResponseTransfer::class, $result);
        $this->assertTrue($result->getIsSuccessful());
    }

    public function testSearchSuccessfullyCallsAlgoliaAndReturnsSearchResponse(): void
    {
        // Arrange
        $query = 'test query';
        $searchParameters = ['filters' => 'category:electronics'];

        $searchClientMock = $this->createSearchClientMock();
        $searchClientMock
            ->expects($this->once())
            ->method('searchSingleIndex')
            ->with(static::TEST_INDEX_NAME, ['query' => $query] + $searchParameters)
            ->willReturn(static::TEST_SEARCH_RESULTS);

        $searchIndexClient = new SearchIndexClient($searchClientMock, static::TEST_INDEX_NAME);

        // Act
        $result = $searchIndexClient->search($query, $searchParameters);

        // Assert
        $this->assertInstanceOf(AlgoliaSearchResponseTransfer::class, $result);
        $this->assertTrue($result->getIsSuccessful());
        $this->assertEquals(static::TEST_SEARCH_RESULTS, $result->getSearchResults());
    }

    protected function createSearchClientMock(): MockObject|SearchClient
    {
        return $this->createMock(SearchClient::class);
    }
}
