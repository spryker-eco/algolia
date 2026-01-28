<?php

/**
 * Copyright © 2022-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Zed\Algolia\Business\Handler;

use Algolia\AlgoliaSearch\Exceptions\BadRequestException;
use Algolia\AlgoliaSearch\Exceptions\NotFoundException;
use Algolia\AlgoliaSearch\SearchClient;
use Algolia\AlgoliaSearch\Support\Helpers;
use Codeception\Test\Unit;
use Exception;
use InvalidArgumentException;

/**
 * Auto-generated group annotations
 *
 * @group PyzTest
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
     * @param \Exception $exception
     * @param bool $isRegionException
     * @param int $methodInvocationNumber
     *
     * @throws \Algolia\AlgoliaSearch\Exceptions\BadRequestException
     *
     * @return void
     */
    public function testCreateSuggestionsIndexWorksProperlyWithExceptionsIfConfigurationExists(
        Exception $exception,
        bool $isRegionException,
        int $methodInvocationNumber
    ): void {
        // Arrange
        $suggestionIndexHandler = $this->tester->getFactory()->createSuggestionIndexHandler();
        $searchClientMock = $this->getSearchClientMock();
        $searchClientMock->expects($this->exactly($methodInvocationNumber))
            ->method('custom')
            ->willReturnCallback(function ($method, $url, $options, $hosts) use ($exception, $isRegionException) {
                if (!$isRegionException || $hosts[0] !== self::QUERY_SUGGESTION_BASE_URL_EU) {
                    throw $exception;
                }

                return true;
            });

        // Assert
        if (!$isRegionException) {
            $this->expectExceptionObject($exception);
        }

        // Act
        $suggestionIndexHandler->createSuggestionsIndex('test', $searchClientMock);
    }

    /**
     * @dataProvider getExceptionDataProvider
     *
     * @param \Exception $exception
     * @param bool $isRegionException
     * @param int $methodInvocationNumber
     *
     * @throws \Algolia\AlgoliaSearch\Exceptions\BadRequestException
     *
     * @return void
     */
    public function testCreateSuggestionsIndexWorksProperlyWithExceptionsIfConfigurationDoesntExist(
        Exception $exception,
        bool $isRegionException,
        int $methodInvocationNumber
    ): void {
        // Arrange
        $suggestionIndexHandler = $this->tester->getFactory()->createSuggestionIndexHandler();
        $searchClientMock = $this->getSearchClientMock();
        $searchClientMock->expects($this->exactly(($isRegionException ? 2 : 1) * $methodInvocationNumber))
            ->method('custom')
            ->willReturnCallback(function ($method, $url, $options, $hosts) use ($exception, $isRegionException) {
                if (!$isRegionException || $hosts[0] !== self::QUERY_SUGGESTION_BASE_URL_EU) {
                    throw $exception;
                }

                if (str_starts_with($url, '/1/configs/')) {
                    throw new NotFoundException();
                }

                return true;
            });

        // Assert
        if (!$isRegionException) {
            $this->expectExceptionObject($exception);
        }

        // Act
        $suggestionIndexHandler->createSuggestionsIndex('test', $searchClientMock);
    }

    /**
     * @dataProvider getExceptionDataProvider
     *
     * @param \Exception $exception
     * @param bool $isRegionException
     * @param int $methodInvocationNumber
     *
     * @throws \Algolia\AlgoliaSearch\Exceptions\BadRequestException
     *
     * @return void
     */
    public function testGetAllConfigurationsWorksProperlyWithExceptions(
        Exception $exception,
        bool $isRegionException,
        int $methodInvocationNumber
    ): void {
        // Arrange
        $suggestionIndexHandler = $this->tester->getFactory()->createSuggestionIndexHandler();
        $searchClientMock = $this->getSearchClientMock();
        $searchClientMock->expects($this->exactly($methodInvocationNumber))
            ->method('custom')
            ->willReturnCallback(function ($method, $url, $options, $hosts) use ($exception, $isRegionException) {
                if (!$isRegionException || $hosts[0] !== self::QUERY_SUGGESTION_BASE_URL_EU) {
                    throw $exception;
                }

                return [];
            });

        // Assert
        if (!$isRegionException) {
            $this->expectExceptionObject($exception);
        }

        // Act
        $suggestionIndexHandler->getAllConfigurations($searchClientMock);
    }

    /**
     * @dataProvider getExceptionDataProvider
     *
     * @param \Exception $exception
     * @param bool $isRegionException
     * @param int $methodInvocationNumber
     *
     * @throws \Algolia\AlgoliaSearch\Exceptions\BadRequestException
     *
     * @return void
     */
    public function testDeleteConfigurationWorksProperlyWithExceptions(
        Exception $exception,
        bool $isRegionException,
        int $methodInvocationNumber
    ): void {
        // Arrange
        $suggestionIndexHandler = $this->tester->getFactory()->createSuggestionIndexHandler();
        $searchClientMock = $this->getSearchClientMock();
        $searchClientMock->expects($this->exactly($methodInvocationNumber))
            ->method('custom')
            ->willReturnCallback(function ($method, $url, $options, $hosts) use ($exception, $isRegionException) {
                if (!$isRegionException || $hosts[0] !== self::QUERY_SUGGESTION_BASE_URL_EU) {
                    throw $exception;
                }

                return [];
            });

        // Assert
        if (!$isRegionException) {
            $this->expectExceptionObject($exception);
        }

        // Act
        $suggestionIndexHandler->deleteConfiguration('test', $searchClientMock);
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
                'exception' => $this->getWrongRegionExceptionMessage(),
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

    /**
     * @return \Exception
     */
    protected function getWrongRegionExceptionMessage(): Exception
    {
        $str = 'any non-json string';
        try {
            Helpers::json_decode($str, true);
        } catch (Exception $e) {
            return $e;
        }

        return new Exception('Failed to get an exception');
    }

    /**
     * @return \Algolia\AlgoliaSearch\SearchClient|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getSearchClientMock(): SearchClient
    {
        return $this->getMockBuilder(SearchClient::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
