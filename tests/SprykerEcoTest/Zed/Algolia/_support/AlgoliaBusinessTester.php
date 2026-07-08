<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Zed\Algolia;

use Algolia\AlgoliaSearch\Api\SearchClient;
use Algolia\AlgoliaSearch\Exceptions\BadRequestException;
use Algolia\AlgoliaSearch\Exceptions\RetriableException;
use Algolia\AlgoliaSearch\Http\HttpClientInterface;
use Codeception\Actor;
use Codeception\Stub\Expected;
use Codeception\Test\Feature\Stub;
use Exception;
use Generated\Shared\Transfer\AlgoliaResponseTransfer;
use GuzzleHttp\Psr7\Stream;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvokedCount as InvokedCountMatcher;
use Psr\Http\Message\ResponseInterface;
use SprykerEco\Zed\Algolia\Business\Api\Client\SearchIndexClientInterface;
use SprykerEco\Zed\Algolia\Business\Api\Creator\SearchClientCreatorInterface;
use SprykerEco\Zed\Algolia\Business\Api\Creator\SearchIndexClientCreatorInterface;
use SprykerEco\Zed\Algolia\Business\Api\IndexConfigurator\IndexConfigurator;
use SprykerEco\Zed\Algolia\Business\Handler\SuggestionIndexHandlerInterface;

/**
 * Inherited Methods
 *
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 * @method \SprykerEco\Zed\Algolia\Business\AlgoliaFacadeInterface getFacade()
 * @method \SprykerEco\Zed\Algolia\Business\AlgoliaBusinessFactory getFactory()
 *
 * @SuppressWarnings(PHPMD)
 */
class AlgoliaBusinessTester extends Actor
{
    use _generated\AlgoliaBusinessTesterActions;

    use Stub;

    public function mockSearchIndexSaveObjects(AlgoliaResponseTransfer $algoliaResponseTransfer): void
    {
        $searchIndexClientMock = $this->createSearchIndexClientMock();

        $searchIndexClientMock->method('saveObjects')
            ->willReturn($algoliaResponseTransfer);

        $this->mockFactoryMethod(
            'createSearchClientCreator',
            $this->createSearchClientCreatorMock($this->createSearchClientMock()),
        );

        $this->mockFactoryMethod(
            'createSearchIndexClientCreator',
            $this->createSearchIndexClientCreatorMock($searchIndexClientMock),
        );
    }

    public function mockSearchClientForCredentialsSuccessfulValidation(string $adminApiKey, string $searchOnlyApiKey): void
    {
        $algoliaSearchClientMock = $this->createSearchClientMock();

        $algoliaSearchClientMock->method('getApiKey')
            ->willReturn(
                [
                    'acl' => ['addObject', 'search'],
                    $adminApiKey => ['value' => $adminApiKey],
                    $searchOnlyApiKey => ['value' => $searchOnlyApiKey],
                ],
            );

        // In v4, credential validation uses setSettings/waitForTask on SearchClient directly
        $algoliaSearchClientMock->method('setSettings')->willReturn(['taskID' => 1]);
        $algoliaSearchClientMock->method('waitForTask')->willReturn(null);
        $algoliaSearchClientMock->method('deleteIndex')->willReturn(null);

        $this->mockFactoryMethod(
            'createSearchClientCreator',
            $this->createSearchClientCreatorMock($algoliaSearchClientMock),
        );
    }

    public function mockSearchClientForCredentialsValidationWhenAdminCredentialsIsWrong(): void
    {
        $algoliaSearchClientMock = $this->createSearchClientMock();

        $algoliaSearchClientMock->method('getApiKey')
            ->willThrowException(new Exception());

        $this->mockFactoryMethod(
            'createSearchClientCreator',
            $this->createSearchClientCreatorMock($algoliaSearchClientMock),
        );
    }

    public function mockSearchClientForCredentialsValidationWhenSearchOnlyCredentialsIsWrong(string $adminApiKey): void
    {
        $algoliaSearchClientMock = $this->createSearchClientMock();

        $matcher = new InvokedCountMatcher(2);

        $algoliaSearchClientMock
            ->expects($matcher)
            ->method('getApiKey')
            ->willReturnCallback(function () use ($matcher, $adminApiKey) {
                if ($matcher->numberOfInvocations() === 1) {
                    return ['acl' => ['addObject'], 'value' => $adminApiKey];
                }

                throw new Exception();
            });

        // In v4, credential validation uses setSettings/waitForTask on SearchClient directly
        $algoliaSearchClientMock->method('setSettings')->willReturn(['taskID' => 1]);
        $algoliaSearchClientMock->method('waitForTask')->willReturn(null);
        $algoliaSearchClientMock->method('deleteIndex')->willReturn(null);

        $this->mockFactoryMethod(
            'createSearchClientCreator',
            $this->createSearchClientCreatorMock($algoliaSearchClientMock),
        );
    }

    public function mockSearchIndexDeleteObjects(AlgoliaResponseTransfer $algoliaResponseTransfer): void
    {
        $searchClientMock = $this->searchClientMockReturnsTwoIndices(
            $this->createSearchClientMock(),
        );

        $searchIndexClientMock = $this->createSearchIndexClientMock();

        $searchIndexClientMock->method('deleteObjects')
            ->willReturn($algoliaResponseTransfer);

        $this->mockFactoryMethod(
            'createSearchClientCreator',
            $this->createSearchClientCreatorMock($searchClientMock),
        );

        $this->mockFactoryMethod(
            'createSearchIndexClientCreator',
            $this->createSearchIndexClientCreatorMock($searchIndexClientMock),
        );
    }

    public function mockSearchIndexClient(SearchIndexClientInterface $searchIndexClientMock): void
    {
        $searchClientMock = $this->searchClientMockReturnsTwoIndices(
            $this->createSearchClientMock(),
        );

        $this->mockFactoryMethod(
            'createSearchClientCreator',
            $this->createSearchClientCreatorMock($searchClientMock),
        );

        $this->mockFactoryMethod(
            'createSearchIndexClientCreator',
            $this->createSearchIndexClientCreatorMock($searchIndexClientMock),
        );
    }

    public function createSearchClientCreatorMock(SearchClient $searchClient): SearchClientCreatorInterface
    {
        $mock = $this->makeEmpty(SearchClientCreatorInterface::class);

        $mock->method('createSearchClientFromConfig')
            ->willReturn($searchClient);

        $mock->method('createSearchClientWithCredentials')
            ->willReturn($searchClient);

        return $mock;
    }

    public function createSearchIndexClientCreatorMock(SearchIndexClientInterface $searchIndexClient): SearchIndexClientCreatorInterface
    {
        $mock = $this->makeEmpty(SearchIndexClientCreatorInterface::class);

        $mock->method('createSearchIndexApiClient')
            ->willReturn($searchIndexClient);

        return $mock;
    }

    public function createSearchIndexClientMock(): SearchIndexClientInterface
    {
        return $this->makeEmpty(SearchIndexClientInterface::class);
    }

    public function createSearchClientMock(): SearchClient
    {
        return $this->makeEmpty(SearchClient::class);
    }

    public function searchClientMockReturnsTwoIndices(SearchClient $searchClient): SearchClient
    {
        $searchClient->method('listIndices')
            ->willReturn(json_decode('{"items":[{"name":"storereference-product-de_DE-relevance","replicas":["storeReference-product-de_DE-relevance-replica"],"createdAt":"2022-07-21T19:53:53.149Z","updatedAt":"2022-08-09T11:36:56.700Z","entries":2,"dataSize":730,"fileSize":1827,"lastBuildTimeS":1,"numberOfPendingTasks":0,"pendingTask":false},{"name":"storereference-product-en_US-relevance","createdAt":"2022-07-21T19:53:42.360Z","updatedAt":"2022-08-09T11:36:56.634Z","entries":2,"dataSize":730,"fileSize":1827,"lastBuildTimeS":1,"numberOfPendingTasks":0,"pendingTask":false}],"nbPages":1}', true));

        return $searchClient;
    }

    public function searchClientMockReturnsZeroIndices(SearchClient $searchClient): SearchClient
    {
        $searchClient->method('listIndices')
            ->willReturn(json_decode('{"items":[],"nbPages":1}', true));

        return $searchClient;
    }

    public function createSearchClientMockForNonExistingIndex(string $indexName): SearchClient
    {
        $searchClientMock = $this->makeEmpty(SearchClient::class);

        $searchClientMock->method('indexExists')->willReturn(false);

        return $searchClientMock;
    }

    public function createSearchClientMockForExistingIndex(string $indexName): SearchClient
    {
        $searchClientMock = $this->makeEmpty(SearchClient::class);

        $searchClientMock->method('indexExists')->willReturn(true);

        return $searchClientMock;
    }

    public function createSearchClientMockThrowingExceptionOnSetSettings(string $indexName, Exception $e): SearchClient
    {
        $searchClientMock = $this->makeEmpty(SearchClient::class);
        $searchClientMock->method('setSettings')->willThrowException($e);

        return $searchClientMock;
    }

    protected function mockSuggestionIndexHandler(): SuggestionIndexHandlerInterface
    {
        return $this->makeEmpty(SuggestionIndexHandlerInterface::class);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject&\Algolia\AlgoliaSearch\Http\HttpClientInterface
     */
    public function haveRateLimitedAlgoliaHttpClient(): HttpClientInterface&MockObject
    {
        $responseString = '{"message":"Too many requests"}';
        $stream = new Stream(fopen('data://text/plain,' . $responseString, 'r'));

        $responseMock = $this->makeEmpty(ResponseInterface::class);
        $responseMock->method('getBody')->willReturn($stream);
        $responseMock->method('getStatusCode')->willReturn(429);

        $httpClientMock = $this->makeEmpty(HttpClientInterface::class);
        $httpClientMock->method('sendRequest')->willReturn($responseMock);

        return $httpClientMock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject&\Algolia\AlgoliaSearch\Http\HttpClientInterface
     */
    public function haveRetriableExceptionAlgoliaHttpClient(): HttpClientInterface&MockObject
    {
        $httpClientMock = $this->makeEmpty(HttpClientInterface::class);
        $httpClientMock->method('sendRequest')->willThrowException(new RetriableException());

        return $httpClientMock;
    }

    /**
     * @return \Algolia\AlgoliaSearch\Api\SearchClient&\PHPUnit\Framework\MockObject\MockObject
     */
    public function haveRateLimitedAlgoliaSearchClient(): SearchClient&MockObject
    {
        $throwRateLimitException = static function () {
            throw new BadRequestException('Too many requests', 429);
        };

        $searchClient = $this->makeEmpty(SearchClient::class, [
            'indexExists' => true,
            'saveObjects' => $throwRateLimitException,
            'deleteObjects' => $throwRateLimitException,
        ]);

        $searchClientCreatorMock = $this->makeEmpty(SearchClientCreatorInterface::class, [
            'createSearchClientWithCredentials' => $searchClient,
            'createSearchClientFromConfig' => $searchClient,
        ]);

        $this->mockFactoryMethod('createSearchClientCreator', $searchClientCreatorMock);

        return $searchClient;
    }

    /**
     * @param \PHPUnit\Framework\MockObject\Rule\InvokedCount $invokedCount
     */
    public function createIndexConfiguratorMock(InvokedCountMatcher $invokedCount): IndexConfigurator
    {
        $indexConfigurator = $this->makeEmpty(IndexConfigurator::class);

        $indexConfigurator->expects($invokedCount)->method('configureIndex');

        return $indexConfigurator;
    }
}
