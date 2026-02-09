<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Client\Algolia;

use Algolia\AlgoliaSearch\Algolia;
use Algolia\AlgoliaSearch\Exceptions\NotFoundException;
use Codeception\Test\Unit;
use Exception;
use Generated\Shared\Transfer\AlgoliaSearchResponseTransfer;
use Generated\Shared\Transfer\FacetCollectionTransfer;
use Generated\Shared\Transfer\FacetEntryTransfer;
use Generated\Shared\Transfer\SearchRequestTransfer;
use Generated\Shared\Transfer\SortingEntryTransfer;
use SprykerEco\Client\Algolia\Api\Creator\SearchClientCreatorInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerEcoTest
 * @group Client
 * @group Algolia
 * @group AlgoliaClientTest
 * Add your own group annotations below this line
 */
class AlgoliaClientTest extends Unit
{
    /**
     * @var string
     */
    protected const TEST_INDEX_NAME = 'testIndexName';

    /**
     * @var \SprykerEcoTest\Client\Algolia\AlgoliaClientTester
     */
    protected $tester;

    protected function _after(): void
    {
        Algolia::resetHttpClient();
    }

    /**
     * @dataProvider getPriceSettingDataProvider
     */
    public function testSearchReturnsSuccessfulResult(bool $withPrices): void
    {
        // Arrange
        $searchRequestTransfer = $this->tester->haveSearchRequestTransfer();
        $normalResponseFixtures = $this->tester->loadNormalSearchResponseFixtures($withPrices)->getSearchResults();
        $searchIndexClientMock = $this->tester->createSearchIndexClientMock();
        $searchIndexClientMock
            ->method('search')
            ->willReturn(
                (new AlgoliaSearchResponseTransfer())
                    ->setIsSuccessful(true)
                    ->setSearchResults($normalResponseFixtures),
            );

        $this->tester->mockSearchIndexClient($searchIndexClientMock);

        // Act
        $algoliaSearchResponseTransfer = $this->tester->getClient()->search($searchRequestTransfer);

        // Assert
        $this->assertTrue($algoliaSearchResponseTransfer->getIsSuccessful());
        $this->assertSame(5, count($algoliaSearchResponseTransfer->getItems()));
        $this->assertSame(3, count($algoliaSearchResponseTransfer->getFacets()));
        $this->assertSame(222, $algoliaSearchResponseTransfer->getPagination()->getNumFound());
        $this->assertSame(1, $algoliaSearchResponseTransfer->getPagination()->getCurrentPage());
        $this->assertSame(12, $algoliaSearchResponseTransfer->getPagination()->getCurrentItemsPerPage());
    }

    /**
     * @return array<array<int, bool>>
     */
    public function getPriceSettingDataProvider(): array
    {
        return [[true], [false]];
    }

    public function testSearchReturnsWithRenderingContentHasCorrectFacetsInAlgoliaSearchResponseTransfer(): void
    {
        // Arrange
        $searchRequestTransfer = $this->tester->haveSearchRequestTransfer();
        $normalResponseFixtures = $this->tester->loadNormalSearchResponseFixtures()->getSearchResults();
        $normalResponseFixtures['renderingContent']['facetOrdering']['facets']['order'] = [
            'category',
            'rating',
        ];
        $searchIndexClientMock = $this->tester->createSearchIndexClientMock();
        $searchIndexClientMock
            ->method('search')
            ->willReturn(
                (new AlgoliaSearchResponseTransfer())
                    ->setIsSuccessful(true)
                    ->setSearchResults($normalResponseFixtures),
            );

        $this->tester->mockSearchIndexClient($searchIndexClientMock);

        // Act
        $algoliaSearchResponseTransfer = $this->tester->getClient()->search($searchRequestTransfer);

        // Assert
        $this->assertTrue($algoliaSearchResponseTransfer->getIsSuccessful());
        $this->assertSame(['category', 'rating'], array_keys($algoliaSearchResponseTransfer->getFacets()));
    }

    public function testSearchPerformedByDefaultIndexWhenAlgoliaNotFoundExceptionWasThrownAndSortHasSpecified(): void
    {
        // Arrange
        $searchRequestTransfer = $this->tester->haveSearchRequestTransfer([
            SearchRequestTransfer::FACETS => (new FacetCollectionTransfer())
                ->addFacet('facet', (new FacetEntryTransfer())->setType('values')),
        ]);
        $searchRequestTransfer->setSort((new SortingEntryTransfer())->setField('unknown_field')->setDirection('asc'));
        $normalResponseFixtures = $this->tester->loadNormalSearchResponseFixtures()->getSearchResults();
        $searchIndexClientMock = $this->tester->createSearchIndexClientMock();

        $matcher = $this->exactly(2);

        $searchIndexClientMock
            ->expects($matcher)
            ->method('search')
            ->willReturnCallback(function () use ($matcher, $normalResponseFixtures) {
                if ($matcher->numberOfInvocations() === 1) {
                    throw new NotFoundException();
                }

                return (new AlgoliaSearchResponseTransfer())
                    ->setIsSuccessful(true)
                    ->setSearchResults($normalResponseFixtures);
            });

        $this->tester->mockSearchIndexClient($searchIndexClientMock);

        // Act
        $algoliaSearchResponseTransfer = $this->tester->getClient()->search($searchRequestTransfer);

        // Assert
        $this->assertTrue($algoliaSearchResponseTransfer->getIsSuccessful());
        $this->assertSame(5, count($algoliaSearchResponseTransfer->getItems()));
        $this->assertSame(3, count($algoliaSearchResponseTransfer->getFacets()));
        $this->assertSame(222, $algoliaSearchResponseTransfer->getPagination()->getNumFound());
        $this->assertSame(1, $algoliaSearchResponseTransfer->getPagination()->getCurrentPage());
        $this->assertSame(12, $algoliaSearchResponseTransfer->getPagination()->getCurrentItemsPerPage());
    }

    public function testSearchReturnsUnsuccessfulResultWhenAlgoliaNotFoundExceptionWasThrownAndSortParamHasNotSpecified(): void
    {
        // Arrange
        $searchRequestTransfer = $this->tester->haveSearchRequestTransfer();
        $searchIndexClientMock = $this->tester->createSearchIndexClientMock();
        $searchIndexClientMock
            ->method('search')
            ->willThrowException(new NotFoundException());

        $this->tester->mockSearchIndexClient($searchIndexClientMock);

        // Act
        $algoliaSearchResponseTransfer = $this->tester->getClient()->search($searchRequestTransfer);

        // Assert
        $this->assertFalse($algoliaSearchResponseTransfer->getIsSuccessful());
        $this->assertSame('The search request has failed. Please check an App configuration or index name in the Algolia account', $algoliaSearchResponseTransfer->getErrors()->offsetGet(0)->getMessage());
        $this->assertSame(424, $algoliaSearchResponseTransfer->getStatusCode());
        $this->assertEmpty($algoliaSearchResponseTransfer->getItems());
        $this->assertEmpty($algoliaSearchResponseTransfer->getFacets());
        $this->assertNull($algoliaSearchResponseTransfer->getPagination());
    }

    public function testSearchReturnsUnsuccessfulResultWhenUnexpectedExceptionWasThrown(): void
    {
        // Arrange
        $searchRequestTransfer = $this->tester->haveSearchRequestTransfer();
        $searchIndexClientMock = $this->tester->createSearchIndexClientMock();
        $searchIndexClientMock
            ->method('search')
            ->willThrowException(new Exception());

        $this->tester->mockSearchIndexClient($searchIndexClientMock);

        // Assert
        $this->expectException(Exception::class);

        // Act
        $this->tester->getClient()->search($searchRequestTransfer);
    }

    public function testSearchSuggestionsReturnsSuccessfulResult(): void
    {
        // Arrange
        $normalResponseFixtures = $this->tester->loadNormalSuggestionsSearchResponseFixtures()->getSearchResults();
        $categoriesResponseFixture = $normalResponseFixtures['categories'];
        unset($normalResponseFixtures['categories']);
        $searchClientMock = $this->tester->createSearchClientMock();
        $searchIndexMock = $this->tester->createSearchIndexMock('test');
        $searchIndexMock->method('searchForFacetValues')
            ->willReturn($categoriesResponseFixture);
        $searchIndexMock->method('search')
            ->willReturnOnConsecutiveCalls($normalResponseFixtures['completions'], $normalResponseFixtures['suggestions']);
        $searchClientMock
            ->method('initIndex')
            ->willReturn($searchIndexMock);

        $this->tester->mockFactoryMethod(
            'createSearchClientCreator',
            $this->tester->createSearchClientCreatorMock($searchClientMock),
        );

        $searchRequestTransfer = $this->tester->haveSearchRequestTransfer();

        // Act
        $suggestionsSearchResponseTransfer = $this->tester->getClient()->searchSuggestions($searchRequestTransfer);

        // Assert
        $this->assertTrue($suggestionsSearchResponseTransfer->getIsSuccessful());
        $this->assertSame(4, count($suggestionsSearchResponseTransfer->getCompletions()));
        $this->assertSame(5, count($suggestionsSearchResponseTransfer->getCategories()));
        $this->assertSame(3, count($suggestionsSearchResponseTransfer->getMatches()));

        $matchesCategory = $suggestionsSearchResponseTransfer->getMatches()['category'];
        $this->assertSame(array_unique($matchesCategory), $matchesCategory);
        $this->assertSame(16, count($suggestionsSearchResponseTransfer->getMatchedItems()));
        $this->assertArrayHasKey('name', $suggestionsSearchResponseTransfer->getMatches());
        $this->assertArrayHasKey('abstract_name', $suggestionsSearchResponseTransfer->getMatches());

        $skus = array_column($suggestionsSearchResponseTransfer->getMatchedItems(), 'sku');
        foreach ($suggestionsSearchResponseTransfer->getMatches() as $match) {
            $this->assertEmpty(array_diff($match, $skus));
        }
    }

    public function testSearchSuggestionsReturnsUnsuccessfulResultWhenAlgoliaNotFoundExceptionWasThrown(): void
    {
        // Arrange
        $searchClientMock = $this->tester->createSearchClientMock();
        $searchClientMock
            ->method('search')
            ->willThrowException(new NotFoundException());
        $this->tester->mockFactoryMethod(
            'createSearchClientCreator',
            $this->tester->createSearchClientCreatorMock($searchClientMock),
        );

        $searchRequestTransfer = $this->tester->haveSearchRequestTransfer();

        // Act
        $suggestionsSearchResponseTransfer = $this->tester->getClient()->searchSuggestions($searchRequestTransfer);

        // Assert
        $this->assertEmpty($suggestionsSearchResponseTransfer->getCompletions());
    }

    public function testSearchSuggestionsReturnsUnsuccessfulResultWhenUnexpectedExceptionWasThrown(): void
    {
        // Arrange
        $searchClientCreatorMock = $this->tester->makeEmpty(
            SearchClientCreatorInterface::class,
            [
                'createSearchClientFromConfig' => function () {
                    throw new Exception();
                },
            ],
        );

        $this->tester->mockFactoryMethod(
            'createSearchClientCreator',
            $searchClientCreatorMock,
        );

        $searchRequestTransfer = $this->tester->haveSearchRequestTransfer();

        // Assert
        $this->expectException(Exception::class);

        // Act
        $this->tester->getClient()->searchSuggestions($searchRequestTransfer);
    }
}
