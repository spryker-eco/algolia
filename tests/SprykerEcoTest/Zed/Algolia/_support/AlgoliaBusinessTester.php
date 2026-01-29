<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Zed\Algolia;

use Algolia\AlgoliaSearch\Exceptions\BadRequestException;
use Algolia\AlgoliaSearch\Exceptions\RetriableException;
use Algolia\AlgoliaSearch\Http\HttpClientInterface;
use Algolia\AlgoliaSearch\Response\AbstractResponse;
use Algolia\AlgoliaSearch\SearchClient;
use Algolia\AlgoliaSearch\SearchIndex;
use Codeception\Actor;
use Codeception\Stub\Expected;
use Codeception\Test\Feature\Stub;
use Exception;
use Generated\Shared\Transfer\AlgoliaResponseTransfer;
use Generated\Shared\Transfer\AlgoliaSearchResponseTransfer;
use GuzzleHttp\Psr7\Stream;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvokedCount as InvokedCountMatcher;
use Psr\Http\Message\ResponseInterface;
use SprykerEco\Zed\Algolia\AlgoliaConfig;
use SprykerEco\Zed\Algolia\Business\Api\Client\SearchIndexClientInterface;
use SprykerEco\Zed\Algolia\Business\Api\Creator\SearchClientCreatorInterface;
use SprykerEco\Zed\Algolia\Business\Api\Creator\SearchIndexClientCreatorInterface;
use SprykerEco\Zed\Algolia\Business\Api\IndexConfigurator\IndexConfigurator;
use SprykerEco\Zed\Algolia\Business\Handler\SuggestionIndexHandlerInterface;
use SprykerEco\Zed\Algolia\Business\IndexResolver\SearchIndexResolver;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\CacheItem;

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

    /**
     * @param \Generated\Shared\Transfer\AlgoliaResponseTransfer $algoliaResponseTransfer
     *
     * @return void
     */
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

        $this->ensureFactoryConfigIsMocked();
    }

    /**
     * @param string $adminApiKey
     * @param string $searchOnlyApiKey
     *
     * @return void
     */
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

        $searchIndexMock = $this->makeEmpty(SearchIndex::class);
        $searchIndexMock->method('setSettings')->willReturn($this->createIndexingResponseMock());
        $searchIndexMock->method('delete');

        $algoliaSearchClientMock->method('initIndex')->willReturn($searchIndexMock);

        $this->mockFactoryMethod(
            'createSearchClientCreator',
            $this->createSearchClientCreatorMock($algoliaSearchClientMock),
        );

        $this->ensureFactoryConfigIsMocked();
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

        $this->ensureFactoryConfigIsMocked();
    }

    /**
     * @param string $adminApiKey
     *
     * @return void
     */
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

        $searchIndexMock = $this->makeEmpty(SearchIndex::class);
        $searchIndexMock->method('setSettings')->willReturn($this->createIndexingResponseMock());
        $searchIndexMock->method('delete');

        $algoliaSearchClientMock->method('initIndex')->willReturn($searchIndexMock);

        $this->mockFactoryMethod(
            'createSearchClientCreator',
            $this->createSearchClientCreatorMock($algoliaSearchClientMock),
        );

        $this->ensureFactoryConfigIsMocked();
    }

    /**
     * @param \Generated\Shared\Transfer\AlgoliaResponseTransfer $algoliaResponseTransfer
     *
     * @return void
     */
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

        $this->ensureFactoryConfigIsMocked();
    }

    /**
     * @param \SprykerEco\Zed\Algolia\Business\Api\Client\SearchIndexClientInterface $searchIndexClientMock
     *
     * @return void
     */
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

        $this->ensureFactoryConfigIsMocked();
    }

    /**
     * @param \Algolia\AlgoliaSearch\SearchClient $searchClient
     *
     * @return \SprykerEco\Zed\Algolia\Business\Api\Creator\SearchClientCreatorInterface
     */
    public function createSearchClientCreatorMock(SearchClient $searchClient): SearchClientCreatorInterface
    {
        $mock = $this->makeEmpty(SearchClientCreatorInterface::class);

        $mock->method('createSearchClientFromConfig')
            ->willReturn($searchClient);

        $mock->method('createSearchClientWithCredentials')
            ->willReturn($searchClient);

        return $mock;
    }

    /**
     * @param \SprykerEco\Zed\Algolia\Business\Api\Client\SearchIndexClientInterface $searchIndexClient
     *
     * @return \SprykerEco\Zed\Algolia\Business\Api\Creator\SearchIndexClientCreatorInterface
     */
    public function createSearchIndexClientCreatorMock(SearchIndexClientInterface $searchIndexClient): SearchIndexClientCreatorInterface
    {
        $mock = $this->makeEmpty(SearchIndexClientCreatorInterface::class);

        $mock->method('createSearchIndexApiClient')
            ->willReturn($searchIndexClient);

        $mock->method('createSearchIndexApiClientForSearch')
            ->willReturn($searchIndexClient);

        return $mock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\SprykerEco\Zed\Algolia\Business\Api\Client\SearchIndexClientInterface
     */
    public function createSearchIndexClientMock(): SearchIndexClientInterface
    {
        return $this->makeEmpty(SearchIndexClientInterface::class);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Algolia\AlgoliaSearch\SearchClient
     */
    public function createSearchClientMock(): SearchClient
    {
        return $this->makeEmpty(SearchClient::class);
    }

    /**
     * @param \PHPUnit\Framework\MockObject\MockObject|\Algolia\AlgoliaSearch\SearchClient $searchClient
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|\Algolia\AlgoliaSearch\SearchClient
     */
    public function searchClientMockReturnsTwoIndices(SearchClient $searchClient): SearchClient
    {
        $searchClient->method('listIndices')
            ->willReturn(json_decode('{"items":[{"name":"storereference-product-de_DE-relevance","replicas":["storeReference-product-de_DE-relevance-replica"],"createdAt":"2022-07-21T19:53:53.149Z","updatedAt":"2022-08-09T11:36:56.700Z","entries":2,"dataSize":730,"fileSize":1827,"lastBuildTimeS":1,"numberOfPendingTasks":0,"pendingTask":false},{"name":"storereference-product-en_US-relevance","createdAt":"2022-07-21T19:53:42.360Z","updatedAt":"2022-08-09T11:36:56.634Z","entries":2,"dataSize":730,"fileSize":1827,"lastBuildTimeS":1,"numberOfPendingTasks":0,"pendingTask":false}],"nbPages":1}', true));

        return $searchClient;
    }

    /**
     * @param \PHPUnit\Framework\MockObject\MockObject|\Algolia\AlgoliaSearch\SearchClient $searchClient
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|\Algolia\AlgoliaSearch\SearchClient
     */
    public function searchClientMockReturnsZeroIndices(SearchClient $searchClient): SearchClient
    {
        $searchClient->method('listIndices')
            ->willReturn(json_decode('{"items":[],"nbPages":1}', true));

        return $searchClient;
    }

    /**
     * @param string $indexName
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|\Algolia\AlgoliaSearch\SearchClient
     */
    public function createSearchClientMockForNonExistingIndex(string $indexName): SearchClient
    {
        $searchClientMock = $this->makeEmpty(SearchClient::class);

        $searchIndexMock = $this->makeEmpty(SearchIndex::class);
        $searchIndexMock->method('exists')->willReturn(false);

        $searchClientMock->expects(Expected::once()->getMatcher())->method('initIndex')->with($indexName)->willReturn($searchIndexMock);

        return $searchClientMock;
    }

    /**
     * @param string $indexName
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|\Algolia\AlgoliaSearch\SearchClient
     */
    public function createSearchClientMockForExistingIndex(string $indexName): SearchClient
    {
        $searchClientMock = $this->makeEmpty(SearchClient::class);

        $searchIndexMock = $this->makeEmpty(SearchIndex::class);
        $searchIndexMock->method('exists')->willReturn(true);

        $searchClientMock->expects(Expected::once()->getMatcher())->method('initIndex')->with($indexName)->willReturn($searchIndexMock);

        return $searchClientMock;
    }

    /**
     * @param \PHPUnit\Framework\MockObject\Rule\InvokedCount $invokedCount
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|\SprykerEco\Zed\Algolia\Business\Api\IndexConfigurator\IndexConfigurator
     */
    public function createIndexConfiguratorMock(InvokedCountMatcher $invokedCount): IndexConfigurator
    {
        $indexConfigurator = $this->makeEmpty(IndexConfigurator::class);

        $indexConfigurator->expects($invokedCount)->method('configureIndex');

        return $indexConfigurator;
    }

    /**
     * @param string $indexName
     * @param \Exception $e
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|\Algolia\AlgoliaSearch\SearchIndex
     */
    public function createSearchIndexMockThrowingExceptionOnSetSettings(string $indexName, Exception $e): SearchIndex
    {
        $searchIndexMock = $this->makeEmpty(SearchIndex::class);
        $searchIndexMock->method('getIndexName')->willReturn($indexName);
        $searchIndexMock->method('setSettings')->willThrowException($e);

        return $searchIndexMock;
    }

    /**
     * @param string $indexName
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|\Algolia\AlgoliaSearch\SearchIndex
     */
    public function createSearchIndexMock(string $indexName): SearchIndex
    {
        $searchIndexMock = $this->makeEmpty(SearchIndex::class);
        $searchIndexMock->method('getIndexName')->willReturn($indexName);

        // IndexResponse is final, but has no additional methods compared to abstract class
        $searchIndexMock
            ->method('setSettings')
            ->willReturn(
                $this->makeEmpty(AbstractResponse::class, [
                    'wait' => function () {
                    },
                ]),
            );

        return $searchIndexMock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\SprykerEco\Zed\Algolia\Business\Handler\SuggestionIndexHandlerInterface
     */
    protected function mockSuggestionIndexHandler(): SuggestionIndexHandlerInterface
    {
        return $this->makeEmpty(SuggestionIndexHandlerInterface::class);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Algolia\AlgoliaSearch\Response\AbstractResponse
     */
    protected function createIndexingResponseMock(): AbstractResponse
    {
        $indexingResponse = $this->makeEmpty(AbstractResponse::class);

        $indexingResponse->method('wait')->willReturn($indexingResponse);

        return $indexingResponse;
    }

    /**
     * @return string
     */
    public function getFixturesSearchResponseDirectory(): string
    {
        return codecept_data_dir('Fixtures/Search/Response/');
    }

    /**
     * @return \Generated\Shared\Transfer\AlgoliaSearchResponseTransfer
     */
    public function loadNormalSearchResponseFixtures(bool $withPrices = true): AlgoliaSearchResponseTransfer
    {
        $searchResponse = json_decode(
            file_get_contents(
                $this->getFixturesSearchResponseDirectory() . ($withPrices ? 'algolia-normal-search-response.json' : 'algolia-normal-search-without-price-with-facet-ordering-response.json'),
            ),
            true,
        );

        return (new AlgoliaSearchResponseTransfer())
            ->setSearchResults($searchResponse);
    }

    /**
     * @return \Generated\Shared\Transfer\AlgoliaSearchResponseTransfer
     */
    public function loadNormalSearchResponseWithFacetOrderingFixtures(): AlgoliaSearchResponseTransfer
    {
        $searchResponse = json_decode(
            file_get_contents(
                $this->getFixturesSearchResponseDirectory() . 'algolia-normal-search-with-facet-ordering-response.json',
            ),
            true,
        );

        return (new AlgoliaSearchResponseTransfer())
            ->setSearchResults($searchResponse);
    }

    /**
     * @return \Generated\Shared\Transfer\AlgoliaSearchResponseTransfer
     */
    public function loadEmptySearchResponseFixtures(): AlgoliaSearchResponseTransfer
    {
        $searchResponse = json_decode(
            file_get_contents(
                $this->getFixturesSearchResponseDirectory() . 'algolia-empty-search-response.json',
            ),
            true,
        );

        return (new AlgoliaSearchResponseTransfer())
            ->setSearchResults($searchResponse);
    }

    /**
     * @return \Generated\Shared\Transfer\AlgoliaSearchResponseTransfer
     */
    public function loadNormalSuggestionsSearchResponseFixtures(): AlgoliaSearchResponseTransfer
    {
        $searchResponse = json_decode(
            file_get_contents(
                $this->getFixturesSearchResponseDirectory() . 'algolia-normal-suggestions-search-response.json',
            ),
            true,
        );
        $result = array_combine(['completions', 'suggestions'], $searchResponse['results']);
        $result['categories'] = $this->loadNormalCategorySuggestionsSearchResponseFixtures();

        return (new AlgoliaSearchResponseTransfer())
            ->setSearchResults($result);
    }

    /**
     * @return \Generated\Shared\Transfer\AlgoliaSearchResponseTransfer
     */
    public function loadEmptyMatchSuggestionsSearchResponseFixtures(): AlgoliaSearchResponseTransfer
    {
        $searchResponse = json_decode(
            file_get_contents(
                $this->getFixturesSearchResponseDirectory() . 'algolia-empty-match-suggestions-search-response.json',
            ),
            true,
        );
        $result = array_combine(['completions', 'suggestions'], $searchResponse['results']);
        $result['categories'] = [];

        return (new AlgoliaSearchResponseTransfer())
            ->setSearchResults($result);
    }

    /**
     * @return \Generated\Shared\Transfer\AlgoliaSearchResponseTransfer
     */
    public function loadEmptySuggestionsSearchResponseFixtures(): AlgoliaSearchResponseTransfer
    {
        $searchResponse = json_decode(
            file_get_contents(
                $this->getFixturesSearchResponseDirectory() . 'algolia-empty-suggestions-search-response.json',
            ),
            true,
        );
        $result = array_combine(['completions', 'suggestions'], $searchResponse['results']);
        $result['categories'] = [];

        return (new AlgoliaSearchResponseTransfer())
            ->setSearchResults($result);
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
     * @return \Algolia\AlgoliaSearch\SearchClient&\PHPUnit\Framework\MockObject\MockObject
     */
    public function haveRateLimitedAlgoliaSearchClient(): SearchClient&MockObject
    {
        $throwRateLimitException = static function () {
            throw new BadRequestException('Too many requests', 429);
        };

        $searchClient = $this->makeEmpty(SearchClient::class, [
            'initIndex' => $this->makeEmpty(SearchIndex::class, [
                'exists' => true,
                'saveObjects' => $throwRateLimitException,
                'deleteObjects' => $throwRateLimitException,
            ]),
        ]);

        $searchClientCreatorMock = $this->makeEmpty(SearchClientCreatorInterface::class, [
            'createSearchClientWithCredentials' => $searchClient,
            'createSearchClientFromConfig' => $searchClient,
        ]);

        $this->mockFactoryMethod('createSearchClientCreator', $searchClientCreatorMock);

        $this->ensureFactoryConfigIsMocked();

        return $searchClient;
    }

    /**
     * @return array
     */
    public function loadNormalCategorySuggestionsSearchResponseFixtures(): array
    {
        return json_decode(
            file_get_contents(
                $this->getFixturesSearchResponseDirectory() . 'algolia-normal-category-search-response.json',
            ),
            true,
        );
    }

    /**
     * @param string $tenantId
     * @param array|null $returnItemData
     *
     * @return void
     */
    public function haveCacheAdapterMock(string $tenantId, ?array $returnItemData = null): void
    {
        $itemMock = new CacheItem();
        $itemMock->set($returnItemData);
        $filesystemAdapterMock = $this->makeEmpty(FilesystemAdapter::class);
        $filesystemAdapterMock->method('getItem')
            ->with(sprintf('algolia.facets.%s', $tenantId))
            ->willReturn($itemMock);

        $this->mockFactoryMethod('createCache', $filesystemAdapterMock);
    }

    /**
     * @param array $settings
     *
     * @return void
     */
    public function haveSearchIndexResolver(array $settings): void
    {
        $searchIndexResolverMock = $this->makeEmpty(SearchIndexResolver::class);
        $searchIndexClientMock = $this->makeEmpty(SearchIndexClientInterface::class);
        $searchIndexClientMock->method('getSettings')->willReturn($settings);
        $searchIndexResolverMock->method('getSearchIndexClientWithPrimarySearchIndex')->willReturn($searchIndexClientMock);

        $this->mockFactoryMethod('createSearchIndexResolver', $searchIndexResolverMock);
    }

    /**
     * Ensures that the factory's getConfig() method is properly mocked to prevent
     * BundleConfigNotFoundException when mocking factory methods.
     *
     * @return void
     */
    public function ensureFactoryConfigIsMocked(): void
    {
        // Create a real instance of AlgoliaConfig and mock it on the factory
        // to prevent BundleConfigNotFoundException when using mocked factories
        $config = new AlgoliaConfig();
        $this->mockFactoryMethod('getConfig', $config);
    }
}
