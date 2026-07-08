<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Zed\Algolia\Business\Handler;

use Algolia\AlgoliaSearch\Api\SearchClient;
use Algolia\AlgoliaSearch\Configuration\SearchConfig;
use Algolia\AlgoliaSearch\Exceptions\BadRequestException;
use Algolia\AlgoliaSearch\Exceptions\NotFoundException;
use Codeception\Test\Unit;
use Exception;
use InvalidArgumentException;

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
     * @var string
     */
    protected const QUERY_SUGGESTION_BASE_URL_EU = 'query-suggestions.eu.algolia.com';

    /**
     * @var \SprykerEcoTest\Zed\Algolia\AlgoliaBusinessTester
     */
    protected $tester;

    /**
     * @dataProvider getExceptionDataProvider
     *
     * @throws \Algolia\AlgoliaSearch\Exceptions\BadRequestException
     */
    public function testCreateSuggestionsIndexWorksProperlyWithExceptionsIfConfigurationExists(
        Exception $exception,
        bool $isRegionException,
        int $methodInvocationNumber
    ): void {
        // Arrange
        $suggestionIndexHandler = $this->tester->getFactory()->createSuggestionIndexHandler();
        $searchClientMock = $this->getSearchClientMock();

        // Assert
        if (!$isRegionException) {
            $this->expectExceptionObject($exception);
        }

        // Act
        $suggestionIndexHandler->createProductSuggestionsIndex('test', $searchClientMock);
    }

    /**
     * @dataProvider getExceptionDataProvider
     *
     * @throws \Algolia\AlgoliaSearch\Exceptions\BadRequestException
     */
    public function testCreateSuggestionsIndexWorksProperlyWithExceptionsIfConfigurationDoesntExist(
        Exception $exception,
        bool $isRegionException,
        int $methodInvocationNumber
    ): void {
        // Arrange
        $suggestionIndexHandler = $this->tester->getFactory()->createSuggestionIndexHandler();
        $searchClientMock = $this->getSearchClientMock();

        // Assert
        if (!$isRegionException) {
            $this->expectExceptionObject($exception);
        }

        // Act
        $suggestionIndexHandler->createProductSuggestionsIndex('test', $searchClientMock);
    }

    /**
     * @dataProvider getExceptionDataProvider
     *
     * @throws \Algolia\AlgoliaSearch\Exceptions\BadRequestException
     */
    public function testGetAllConfigurationsWorksProperlyWithExceptions(
        Exception $exception,
        bool $isRegionException,
        int $methodInvocationNumber
    ): void {
        // Arrange
        $suggestionIndexHandler = $this->tester->getFactory()->createSuggestionIndexHandler();
        $searchClientMock = $this->getSearchClientMock();

        // Assert
        if (!$isRegionException) {
            $this->expectExceptionObject($exception);
        }

        // Act
        $suggestionIndexHandler->getAllConfigurations($searchClientMock);
    }

    /**
     * @return array
     */
    public function getExceptionDataProvider(): array
    {
        return [
            'common exception' => [
                'exception' => new Exception('common exception'),
                'isRegionException' => false,
                'methodInvocationNumber' => 1,
            ],
            'bad request caused by wrong region' => [
                'exception' => new BadRequestException('The log processing region does not match'),
                'isRegionException' => true,
                'methodInvocationNumber' => 2,
            ],
            'bad request for other reason' => [
                'exception' => new BadRequestException('any other bad request'),
                'isRegionException' => false,
                'methodInvocationNumber' => 1,
            ],
            'invalid argument caused by wrong region' => [
                'exception' => new InvalidArgumentException('json_decode_error'),
                'isRegionException' => true,
                'methodInvocationNumber' => 2,
            ],
            'invalid argument for other reason' => [
                'exception' => new InvalidArgumentException('any other invalid argument'),
                'isRegionException' => false,
                'methodInvocationNumber' => 1,
            ],
        ];
    }

    protected function getSearchClientMock(): SearchClient
    {
        $configMock = SearchConfig::create('test-app-id', 'test-api-key');

        $searchClientMock = $this->getMockBuilder(SearchClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $searchClientMock->method('getClientConfig')
            ->willReturn($configMock);

        return $searchClientMock;
    }
}
