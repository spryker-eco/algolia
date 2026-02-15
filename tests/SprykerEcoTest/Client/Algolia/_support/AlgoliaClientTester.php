<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerEcoTest\Client\Algolia;

use Algolia\AlgoliaSearch\Response\AbstractResponse;
use Algolia\AlgoliaSearch\SearchClient;
use Algolia\AlgoliaSearch\SearchIndex;
use Codeception\Actor;
use Codeception\Test\Feature\Stub;
use Generated\Shared\Transfer\AlgoliaSearchResponseTransfer;
use SprykerEco\Client\Algolia\Api\Client\SearchIndexClientInterface;
use SprykerEco\Client\Algolia\Api\Creator\SearchClientCreatorInterface;
use SprykerEco\Client\Algolia\Api\Creator\SearchIndexClientCreatorInterface;
use SprykerEco\Client\Algolia\IndexResolver\SearchIndexResolver;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\CacheItem;

/**
 * Inherited Methods
 *
 * @method void wantTo($text)
 * @method void wantToTest($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause($vars = [])
 *
 * @SuppressWarnings(PHPMD)
 */
class AlgoliaClientTester extends Actor
{
    use _generated\AlgoliaClientTesterActions;

    use Stub;

    public function getFixturesSearchResponseDirectory(): string
    {
        return codecept_data_dir('Fixtures/Search/Response/');
    }

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
     * @param array|null $returnItemData
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
     */
    public function haveSearchIndexResolver(array $settings): void
    {
        $searchIndexResolverMock = $this->makeEmpty(SearchIndexResolver::class);
        $searchIndexClientMock = $this->makeEmpty(SearchIndexClientInterface::class);
        $searchIndexClientMock->method('getSettings')->willReturn($settings);
        $searchIndexResolverMock->method('getSearchIndexClientWithPrimarySearchIndex')->willReturn($searchIndexClientMock);

        $this->mockFactoryMethod('createSearchIndexResolver', $searchIndexResolverMock);
    }

    public function createSearchClientMock(): SearchClient
    {
        return $this->makeEmpty(SearchClient::class);
    }

    public function createSearchIndexClientMock(): SearchIndexClientInterface
    {
        return $this->makeEmpty(SearchIndexClientInterface::class);
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

    public function searchClientMockReturnsTwoIndices(SearchClient $searchClient): SearchClient
    {
        $searchClient->method('listIndices')
            ->willReturn(json_decode('{"items":[{"name":"storereference-product-de_DE-relevance","replicas":["storeReference-product-de_DE-relevance-replica"],"createdAt":"2022-07-21T19:53:53.149Z","updatedAt":"2022-08-09T11:36:56.700Z","entries":2,"dataSize":730,"fileSize":1827,"lastBuildTimeS":1,"numberOfPendingTasks":0,"pendingTask":false},{"name":"storereference-product-en_US-relevance","createdAt":"2022-07-21T19:53:42.360Z","updatedAt":"2022-08-09T11:36:56.634Z","entries":2,"dataSize":730,"fileSize":1827,"lastBuildTimeS":1,"numberOfPendingTasks":0,"pendingTask":false}],"nbPages":1}', true));

        return $searchClient;
    }

    public function createSearchIndexClientCreatorMock(SearchIndexClientInterface $searchIndexClient): SearchIndexClientCreatorInterface
    {
        $mock = $this->makeEmpty(SearchIndexClientCreatorInterface::class);

        $mock->method('createSearchIndexApiClientForSearch')
            ->willReturn($searchIndexClient);

        return $mock;
    }
}
