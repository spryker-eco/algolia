<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Zed\Algolia\Business\Handler;

use Algolia\AlgoliaSearch\Api\QuerySuggestionsClient;
use Algolia\AlgoliaSearch\Api\SearchClient;
use Algolia\AlgoliaSearch\Exceptions\BadRequestException;
use Algolia\AlgoliaSearch\Exceptions\NotFoundException;
use Codeception\Test\Unit;
use SprykerEco\Zed\Algolia\Business\Handler\SuggestionIndexHandler;

/**
 * Auto-generated group annotations
 *
 * @group SprykerEcoTest
 * @group Zed
 * @group Algolia
 * @group Business
 * @group Handler
 * @group SuggestionIndexHandlerExceptionHandlingTest
 * Add your own group annotations below this line
 */
class SuggestionIndexHandlerExceptionHandlingTest extends Unit
{
    /**
     * @var \SprykerEcoTest\Zed\Algolia\AlgoliaBusinessTester
     */
    protected $tester;

    public function testCreateSuggestionsIndexReturnsEarlyWhenConfigurationExists(): void
    {
        // Arrange
        $querySuggestionsClientMock = $this->createMock(QuerySuggestionsClient::class);
        $querySuggestionsClientMock->method('getConfig')->willReturn(['indexName' => 'test_query_suggestions']);
        $querySuggestionsClientMock->expects($this->never())->method('createConfig');

        $handler = $this->createHandlerWithMockedQuerySuggestionsClient($querySuggestionsClientMock);

        // Act
        $handler->createProductSuggestionsIndex('test', $this->createMock(SearchClient::class));
    }

    public function testCreateSuggestionsIndexCreatesConfigWhenConfigurationDoesNotExist(): void
    {
        // Arrange
        $querySuggestionsClientMock = $this->createMock(QuerySuggestionsClient::class);
        $querySuggestionsClientMock->method('getConfig')->willThrowException(new NotFoundException('not found', 404));
        $querySuggestionsClientMock->expects($this->once())->method('createConfig');

        $handler = $this->createHandlerWithMockedQuerySuggestionsClient($querySuggestionsClientMock);

        // Act
        $handler->createProductSuggestionsIndex('test', $this->createMock(SearchClient::class));
    }

    public function testGetAllConfigurationsDelegatesToQuerySuggestionsClient(): void
    {
        // Arrange
        $expectedConfigs = [['indexName' => 'test_query_suggestions']];
        $querySuggestionsClientMock = $this->createMock(QuerySuggestionsClient::class);
        $querySuggestionsClientMock->method('getAllConfigs')->willReturn($expectedConfigs);

        $handler = $this->createHandlerWithMockedQuerySuggestionsClient($querySuggestionsClientMock);

        // Act
        $result = $handler->getAllConfigurations($this->createMock(SearchClient::class));

        // Assert
        $this->assertSame($expectedConfigs, $result);
    }

    public function testGetAllConfigurationsRetriesWithEuRegionWhenUsRegionCallFails(): void
    {
        // Arrange
        $expectedConfigs = [['indexName' => 'test_query_suggestions']];

        $usQuerySuggestionsClientMock = $this->createMock(QuerySuggestionsClient::class);
        $usQuerySuggestionsClientMock->method('getAllConfigs')->willThrowException(new BadRequestException('307: Temporary Redirect'));

        $euQuerySuggestionsClientMock = $this->createMock(QuerySuggestionsClient::class);
        $euQuerySuggestionsClientMock->method('getAllConfigs')->willReturn($expectedConfigs);

        $handler = $this->getMockBuilder(SuggestionIndexHandler::class)
            ->onlyMethods(['createQuerySuggestionsClientForRegion'])
            ->getMock();

        $handler->method('createQuerySuggestionsClientForRegion')
            ->willReturnOnConsecutiveCalls($usQuerySuggestionsClientMock, $euQuerySuggestionsClientMock);

        // Act
        $result = $handler->getAllConfigurations($this->createMock(SearchClient::class));

        // Assert
        $this->assertSame($expectedConfigs, $result);
    }

    public function testGetAllConfigurationsRethrowsExceptionWhenBothRegionsFail(): void
    {
        // Arrange
        $usQuerySuggestionsClientMock = $this->createMock(QuerySuggestionsClient::class);
        $usQuerySuggestionsClientMock->method('getAllConfigs')->willThrowException(new BadRequestException('us region failed'));

        $euQuerySuggestionsClientMock = $this->createMock(QuerySuggestionsClient::class);
        $euException = new BadRequestException('eu region failed');
        $euQuerySuggestionsClientMock->method('getAllConfigs')->willThrowException($euException);

        $handler = $this->getMockBuilder(SuggestionIndexHandler::class)
            ->onlyMethods(['createQuerySuggestionsClientForRegion'])
            ->getMock();

        $handler->method('createQuerySuggestionsClientForRegion')
            ->willReturnOnConsecutiveCalls($usQuerySuggestionsClientMock, $euQuerySuggestionsClientMock);

        $this->expectExceptionObject($euException);

        // Act
        $handler->getAllConfigurations($this->createMock(SearchClient::class));
    }

    protected function createHandlerWithMockedQuerySuggestionsClient(
        QuerySuggestionsClient $querySuggestionsClient,
    ): SuggestionIndexHandler {
        $handler = $this->getMockBuilder(SuggestionIndexHandler::class)
            ->onlyMethods(['createQuerySuggestionsClientForRegion'])
            ->getMock();

        $handler->method('createQuerySuggestionsClientForRegion')
            ->willReturn($querySuggestionsClient);

        return $handler;
    }
}
