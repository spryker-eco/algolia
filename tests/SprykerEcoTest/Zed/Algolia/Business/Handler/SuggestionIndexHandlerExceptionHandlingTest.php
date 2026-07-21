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
use SprykerEco\Zed\Algolia\AlgoliaConfig;
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

    public function testCreateSuggestionsIndexRetriesWithEuRegionWhenUsRegionCallFails(): void
    {
        // Arrange
        $usQuerySuggestionsClientMock = $this->createMock(QuerySuggestionsClient::class);
        $usQuerySuggestionsClientMock->method('getConfig')->willThrowException(new BadRequestException('307: Temporary Redirect'));

        $euQuerySuggestionsClientMock = $this->createMock(QuerySuggestionsClient::class);
        $euQuerySuggestionsClientMock->method('getConfig')->willReturn(['indexName' => 'test_query_suggestions']);
        $euQuerySuggestionsClientMock->expects($this->never())->method('createConfig');

        $algoliaConfigMock = $this->createMock(AlgoliaConfig::class);
        $algoliaConfigMock->method('getSuggestionGenerateAttributes')->willReturn([['category'], ['attributes.brand']]);

        $handler = $this->getMockBuilder(SuggestionIndexHandler::class)
            ->setConstructorArgs([$algoliaConfigMock])
            ->onlyMethods(['createQuerySuggestionsClientForRegion'])
            ->getMock();

        $handler->method('createQuerySuggestionsClientForRegion')
            ->willReturnOnConsecutiveCalls($usQuerySuggestionsClientMock, $euQuerySuggestionsClientMock);

        // Act
        $handler->createProductSuggestionsIndex('test', $this->createMock(SearchClient::class));
    }

    public function testCreateSuggestionsIndexRethrowsExceptionWhenBothRegionsFail(): void
    {
        // Arrange
        $usQuerySuggestionsClientMock = $this->createMock(QuerySuggestionsClient::class);
        $usQuerySuggestionsClientMock->method('getConfig')->willThrowException(new BadRequestException('us region failed'));

        $euQuerySuggestionsClientMock = $this->createMock(QuerySuggestionsClient::class);
        $euException = new BadRequestException('eu region failed');
        $euQuerySuggestionsClientMock->method('getConfig')->willThrowException($euException);

        $algoliaConfigMock = $this->createMock(AlgoliaConfig::class);
        $algoliaConfigMock->method('getSuggestionGenerateAttributes')->willReturn([['category'], ['attributes.brand']]);

        $handler = $this->getMockBuilder(SuggestionIndexHandler::class)
            ->setConstructorArgs([$algoliaConfigMock])
            ->onlyMethods(['createQuerySuggestionsClientForRegion'])
            ->getMock();

        $handler->method('createQuerySuggestionsClientForRegion')
            ->willReturnOnConsecutiveCalls($usQuerySuggestionsClientMock, $euQuerySuggestionsClientMock);

        $this->expectExceptionObject($euException);

        // Act
        $handler->createProductSuggestionsIndex('test', $this->createMock(SearchClient::class));
    }

    protected function createHandlerWithMockedQuerySuggestionsClient(
        QuerySuggestionsClient $querySuggestionsClient,
    ): SuggestionIndexHandler {
        $algoliaConfigMock = $this->createMock(AlgoliaConfig::class);
        $algoliaConfigMock->method('getSuggestionGenerateAttributes')->willReturn([['category'], ['attributes.brand']]);

        $handler = $this->getMockBuilder(SuggestionIndexHandler::class)
            ->onlyMethods(['createQuerySuggestionsClientForRegion'])
            ->setConstructorArgs([$algoliaConfigMock])
            ->getMock();

        $handler->method('createQuerySuggestionsClientForRegion')
            ->willReturn($querySuggestionsClient);

        return $handler;
    }
}
