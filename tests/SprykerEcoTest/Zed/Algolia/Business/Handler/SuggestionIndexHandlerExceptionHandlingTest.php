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
use Exception;
use InvalidArgumentException;
use ReflectionMethod;
use SprykerEco\Zed\Algolia\Business\Handler\SuggestionIndexHandler;
use Throwable;

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

    /**
     * @dataProvider regionMismatchExceptionDataProvider
     */
    public function testIsRegionMismatchExceptionIdentifiesRegionExceptionsCorrectly(
        Throwable $exception,
        bool $expectedResult,
    ): void {
        // Arrange
        $handler = new SuggestionIndexHandler();
        $reflection = new ReflectionMethod($handler, 'isRegionMismatchException');

        // Act
        $result = $reflection->invoke($handler, $exception);

        // Assert
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function regionMismatchExceptionDataProvider(): array
    {
        return [
            'bad request caused by wrong region' => [
                'exception' => new BadRequestException('The log processing region does not match'),
                'expectedResult' => true,
            ],
            'bad request for other reason' => [
                'exception' => new BadRequestException('any other bad request'),
                'expectedResult' => false,
            ],
            'invalid argument caused by wrong region (json_decode_error)' => [
                'exception' => new InvalidArgumentException('json_decode_error'),
                'expectedResult' => true,
            ],
            'invalid argument for other reason' => [
                'exception' => new InvalidArgumentException('any other invalid argument'),
                'expectedResult' => false,
            ],
            'common exception' => [
                'exception' => new Exception('common exception'),
                'expectedResult' => false,
            ],
        ];
    }

    protected function createHandlerWithMockedQuerySuggestionsClient(
        QuerySuggestionsClient $querySuggestionsClient,
    ): SuggestionIndexHandler {
        $handler = $this->getMockBuilder(SuggestionIndexHandler::class)
            ->onlyMethods(['createQuerySuggestionsClient'])
            ->getMock();

        $handler->method('createQuerySuggestionsClient')
            ->willReturn($querySuggestionsClient);

        return $handler;
    }
}
