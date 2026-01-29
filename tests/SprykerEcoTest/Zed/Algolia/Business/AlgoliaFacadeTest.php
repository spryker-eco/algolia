<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Zed\Algolia\Business;

use Algolia\AlgoliaSearch\Algolia;
use Algolia\AlgoliaSearch\Exceptions\NotFoundException;
use Algolia\AlgoliaSearch\Exceptions\UnreachableException;
use ArrayObject;
use Codeception\Test\Unit;
use Exception;
use Generated\Shared\Transfer\AlgoliaResponseTransfer;
use Generated\Shared\Transfer\AlgoliaSearchResponseTransfer;
use Generated\Shared\Transfer\FacetCollectionTransfer;
use Generated\Shared\Transfer\FacetEntryTransfer;
use Generated\Shared\Transfer\ProductConcreteTransfer;
use Generated\Shared\Transfer\ProductDeletedTransfer;
use Generated\Shared\Transfer\SearchRequestTransfer;
use Generated\Shared\Transfer\SortingEntryTransfer;
use SprykerEco\Zed\Algolia\AlgoliaConfig;
use SprykerEco\Zed\Algolia\Business\Api\Creator\SearchClientCreatorInterface;

/**
 * Auto-generated group annotations
 *
 * @group PyzTest
 * @group Zed
 * @group Algolia
 * @group Business
 * @group Facade
 * @group AlgoliaFacadeTest
 * Add your own group annotations below this line
 */
class AlgoliaFacadeTest extends Unit
{
    /**
     * @var string
     */
    protected const TEST_INDEX_NAME = 'testIndexName';

    /**
     * @var \SprykerEcoTest\Zed\Algolia\AlgoliaBusinessTester
     */
    protected $tester;

    /**
     * @return void
     */
    protected function _after()
    {
        Algolia::resetHttpClient();
    }

    /**
     * @return void
     */
    public function testExportProductsWithCorrectDataReturnsSuccessfulResponse(): void
    {
        // Arrange
        $this->tester->mockSearchIndexSaveObjects((new AlgoliaResponseTransfer())->setIsSuccessful(true));

        $productConcreteTransfers = new ArrayObject();
        $productConcreteTransfers->append($this->tester->haveFullProductConcreteTransfer([
                ProductConcreteTransfer::NAME => 'product1',
                ProductConcreteTransfer::SKU => 'product1Sku',
            ]));
        $productConcreteTransfers->append($this->tester->haveFullProductConcreteTransfer([
                ProductConcreteTransfer::NAME => 'product2',
                ProductConcreteTransfer::SKU => 'product2Sku',
            ]));

        // Act
        $algoliaResponseTransfer = $this->tester->getFacade()->updateProducts($productConcreteTransfers);

        // Assert
        $this->assertTrue($algoliaResponseTransfer->getIsSuccessful());
    }

    /**
     * @return void
     */
    public function testExportProductsWithInactiveProductsDataReturnsSuccessfulResponse(): void
    {
        // Arrange
        $this->tester->mockSearchIndexSaveObjects((new AlgoliaResponseTransfer())->setIsSuccessful(true));
        $productConcreteTransfers = new ArrayObject();
        $productConcreteTransfers->append(
            $this->tester->haveFullProductConcreteTransfer([ProductConcreteTransfer::NAME => 'product1', ProductConcreteTransfer::SKU => 'product1Sku'])->setIsActive(false),
        );

        // Act
        $algoliaResponseTransfer = $this->tester->getFacade()->updateProducts($productConcreteTransfers);

        // Assert
        $this->assertTrue($algoliaResponseTransfer->getIsSuccessful());
    }

    /**
     * @return void
     */
    public function testUpdateProductsWithInactiveProductsCallsProductDeleterAndReturnsSuccessfulResponse(): void
    {
        // Arrange
        $searchIndexClientMock = $this->tester->createSearchIndexClientMock();
        $searchIndexClientMock->expects($this->exactly(2))
            ->method('deleteObjects')->willReturn((new AlgoliaResponseTransfer())->setIsSuccessful(true));

        $this->tester->mockSearchIndexClient($searchIndexClientMock);

        $productConcreteTransfers = new ArrayObject();
        $productConcreteTransfers->append($this->tester->haveFullProductConcreteTransfer([
                ProductConcreteTransfer::NAME => 'product1',
                ProductConcreteTransfer::SKU => 'product1Sku',
            ])->setIsActive(false));
        $productConcreteTransfers->append($this->tester->haveFullProductConcreteTransfer([
                ProductConcreteTransfer::NAME => 'product2',
                ProductConcreteTransfer::SKU => 'product2Sku',
            ])->setIsActive(false));

        // Act
        $algoliaResponseTransfer = $this->tester->getFacade()->updateProducts($productConcreteTransfers);

        // Assert
        $this->assertTrue($algoliaResponseTransfer->getIsSuccessful());
    }

    /**
     * @group currentGroup
     *
     * @return void
     */
    public function testDeleteProductsWithCorrectDataReturnsSuccessfulResponse(): void
    {
        // Arrange
        // This test mostly checks configuration and overall flow correctness; we don't really have to test API client
        $this->tester->mockSearchIndexDeleteObjects((new AlgoliaResponseTransfer())->setIsSuccessful(true));

        $productDeletedTransfer = (new ProductDeletedTransfer())
            ->setSku('product3Sku');

        // Assert (Checking for no exception were thrown)
        $this->expectNotToPerformAssertions();

        // Act
        $this->tester->getFacade()->deleteProduct($productDeletedTransfer);
    }

    /**
     * @return void
     */
    public function testExportProductsWithEmptySkuReturnsErrorResponse(): void
    {
        // Arrange
        $this->tester->mockSearchIndexSaveObjects((new AlgoliaResponseTransfer())->setIsSuccessful(false));

        $productConcreteTransfers = new ArrayObject();
        $productConcreteTransfers->append($this->tester->haveFullProductConcreteTransfer([ProductConcreteTransfer::NAME => 'product1', ProductConcreteTransfer::SKU => '']));
        $productConcreteTransfers->append($this->tester->haveFullProductConcreteTransfer([ProductConcreteTransfer::NAME => 'product2', ProductConcreteTransfer::SKU => '']));

        // Act
        $algoliaResponseTransfer = $this->tester->getFacade()->updateProducts($productConcreteTransfers);

        // Assert
        $this->assertFalse($algoliaResponseTransfer->getIsSuccessful());
    }

    /**
     * @return void
     */
    public function testValidateApiCredentialsSuccessResponse(): void
    {
        // Arrange
        $algoliaConfigTransfer = $this->tester->haveDefaultAlgoliaConfigTransfer();
        $this->tester->mockSearchClientForCredentialsSuccessfulValidation(
            $algoliaConfigTransfer->getAdminApiKey(),
            $algoliaConfigTransfer->getSearchOnlyApiKey(),
        );

        // Act
        $algoliaApiCredentialsValidationTransfer = $this->tester->getFacade()->validateApiCredentials($algoliaConfigTransfer);

        // Assert
        $this->assertTrue(
            $algoliaApiCredentialsValidationTransfer->getIsAdminApiKeyValid(),
        );

        $this->assertTrue(
            $algoliaApiCredentialsValidationTransfer->getIsSearchOnlyApiKeyValid(),
        );
    }

    /**
     * @return void
     */
    public function testValidateApiCredentialsErrorResponseWhenAdminApiKeyInvalid(): void
    {
        // Arrange
        $algoliaConfigTransfer = $this->tester->haveDefaultAlgoliaConfigTransfer();
        $this->tester->mockSearchClientForCredentialsValidationWhenAdminCredentialsIsWrong();

        // Act
        $algoliaApiCredentialsValidationTransfer = $this->tester->getFacade()->validateApiCredentials($algoliaConfigTransfer);

        // Assert
        $this->assertFalse($algoliaApiCredentialsValidationTransfer->getIsAdminApiKeyValid());
    }

    /**
     * @return void
     */
    public function testValidateApiCredentialsErrorResponseWhenSearchOnlyKeyInvalid(): void
    {
        // Arrange
        $algoliaConfigTransfer = $this->tester->haveDefaultAlgoliaConfigTransfer();
        $this->tester->mockSearchClientForCredentialsValidationWhenSearchOnlyCredentialsIsWrong($algoliaConfigTransfer->getAdminApiKey());

        // Act
        $algoliaApiCredentialsValidationTransfer = $this->tester->getFacade()->validateApiCredentials($algoliaConfigTransfer);

        // Assert
        $this->assertFalse($algoliaApiCredentialsValidationTransfer->getIsSearchOnlyApiKeyValid());
    }

    /**
     * @return void
     */
    public function testExportProductsThrowsBadRequestExceptionOnRateLimitDuringIndexSetup(): void
    {
        $productConcreteTransfers = new ArrayObject();
        $productConcreteTransfers->append($this->tester->haveFullProductConcreteTransfer([
                ProductConcreteTransfer::NAME => 'product1',
                ProductConcreteTransfer::SKU => 'product1Sku',
            ]));

        Algolia::setHttpClient(
            $this->tester->haveRateLimitedAlgoliaHttpClient(),
        );

        $this->expectExceptionObject(
            $this->tester->getRateLimitExceptionExample(),
        );

        $this->tester->getFacade()->updateProducts($productConcreteTransfers);
    }

    /**
     * @return void
     */
    public function testExportProductsReportsFailureWithExceptionOnRateLimitSaveObject(): void
    {
        $productConcreteTransfers = new ArrayObject();
        $productConcreteTransfers->append($this->tester->haveFullProductConcreteTransfer([
                ProductConcreteTransfer::NAME => 'product1',
                ProductConcreteTransfer::SKU => 'product1Sku',
            ]));

        $this->tester->haveRateLimitedAlgoliaSearchClient();
        $this->expectExceptionObject($this->tester->getRateLimitExceptionExample());

        $this->tester->getFacade()->updateProducts($productConcreteTransfers);
    }

    /**
     * @return void
     */
    public function testDeleteProductThrowsBadRequestExceptionOnRateLimitSaveObject(): void
    {
        // Arrange
        $productDeletedTransfer = (new ProductDeletedTransfer())
            ->setSku('product1sku');

        $mockSearchClient = $this->tester->haveRateLimitedAlgoliaSearchClient();
        $mockSearchClient->method('listIndices')->willReturn([
            'items' => [
                [
                    'name' => 'movies_product_' . strtolower('test_tenant'),
                    'replicas' => [], //deletion only happens on indexes with replicas (primary indexes)
                ],
            ],
        ]);

        $this->tester->mockConfigMethod('getTenantIdentifier', 'test_tenant');

        // Assert
        $this->expectExceptionObject($this->tester->getRateLimitExceptionExample());

        // Act
        $this->tester->getFacade()->deleteProduct($productDeletedTransfer);
    }

    /**
     * Algolia's API wrapper will raise a `UnreachableException` if they are unable to send the request due to a server or connection problem.
     * This test ensures that if Algolia changes this behavior, our test suite will fail.
     *
     * @return void
     */
    public function testRetriableExceptionIsConvertedToUnreachableExceptionByAlgoliaRetryApiWrapper(): void
    {
        $productConcreteTransfers = new ArrayObject();
        $productConcreteTransfers->append(
            $this->tester->haveFullProductConcreteTransfer([
                ProductConcreteTransfer::NAME => 'product1',
                ProductConcreteTransfer::SKU => 'product1Sku',
            ]),
        );

        Algolia::setHttpClient(
            $this->tester->haveRetriableExceptionAlgoliaHttpClient(),
        );

        $this->expectExceptionObject(
            new UnreachableException(),
        );

        $this->tester->getFacade()->updateProducts($productConcreteTransfers);
    }

    /**
     * @dataProvider getPriceSettingDataProvider
     *
     * @return void
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
        $algoliaSearchResponseTransfer = $this->tester->getFacade()->search($searchRequestTransfer);

        // Assert
        $this->assertTrue($algoliaSearchResponseTransfer->getIsSuccessful());
        $this->assertSame(5, count($algoliaSearchResponseTransfer->getItems()));
        $this->assertSame(3, count($algoliaSearchResponseTransfer->getFacets()));
        $this->assertSame(222, $algoliaSearchResponseTransfer->getPagination()->getNumFound());
        $this->assertSame(1, $algoliaSearchResponseTransfer->getPagination()->getCurrentPage());
        $this->assertSame(12, $algoliaSearchResponseTransfer->getPagination()->getCurrentItemsPerPage());
    }

    /**
     * @return array<array<int, string>>
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
        $algoliaSearchResponseTransfer = $this->tester->getFacade()->search($searchRequestTransfer);

        // Assert
        $this->assertTrue($algoliaSearchResponseTransfer->getIsSuccessful());
        $this->assertSame(['category', 'rating'], array_keys($algoliaSearchResponseTransfer->getFacets()));
    }

    /**
     * @return void
     */
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
        $algoliaSearchResponseTransfer = $this->tester->getFacade()->search($searchRequestTransfer);

        // Assert
        $this->assertTrue($algoliaSearchResponseTransfer->getIsSuccessful());
        $this->assertSame(5, count($algoliaSearchResponseTransfer->getItems()));
        $this->assertSame(3, count($algoliaSearchResponseTransfer->getFacets()));
        $this->assertSame(222, $algoliaSearchResponseTransfer->getPagination()->getNumFound());
        $this->assertSame(1, $algoliaSearchResponseTransfer->getPagination()->getCurrentPage());
        $this->assertSame(12, $algoliaSearchResponseTransfer->getPagination()->getCurrentItemsPerPage());
    }

    /**
     * @return void
     */
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
        $algoliaSearchResponseTransfer = $this->tester->getFacade()->search($searchRequestTransfer);

        // Assert
        $this->assertFalse($algoliaSearchResponseTransfer->getIsSuccessful());
        $this->assertSame('The search request has failed. Please check an App configuration or index name in the Algolia account', $algoliaSearchResponseTransfer->getErrors()->offsetGet(0)->getMessage());
        $this->assertSame(424, $algoliaSearchResponseTransfer->getStatusCode());
        $this->assertEmpty($algoliaSearchResponseTransfer->getItems());
        $this->assertEmpty($algoliaSearchResponseTransfer->getFacets());
        $this->assertNull($algoliaSearchResponseTransfer->getPagination());
    }

    /**
     * @return void
     */
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
        $this->tester->getFacade()->search($searchRequestTransfer);
    }

    /**
     * @return void
     */
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

        $this->tester->ensureFactoryConfigIsMocked();

        //$algoliaConfigTransfer = $this->tester->havePersistedAlgoliaConfig(['isSearchInFrontendEnabled' => true]);
        $searchRequestTransfer = $this->tester->haveSearchRequestTransfer();

        // Act
        $suggestionsSearchResponseTransfer = $this->tester->getFacade()->searchSuggestions($searchRequestTransfer);

        // Assert
        $this->assertTrue($suggestionsSearchResponseTransfer->getIsSuccessful());
        $this->assertSame(4, count($suggestionsSearchResponseTransfer->getCompletions()));
        $this->assertSame(5, count($suggestionsSearchResponseTransfer->getCategories()));
        $this->assertSame(3, count($suggestionsSearchResponseTransfer->getMatches()));

        $matchesCategory = $suggestionsSearchResponseTransfer->getMatches()[AlgoliaConfig::INDEXED_PRODUCT_FIELD_NAME_CATEGORY];
        $this->assertSame(array_unique($matchesCategory), $matchesCategory);
        $this->assertSame(16, count($suggestionsSearchResponseTransfer->getMatchedItems()));
        $this->assertArrayHasKey(AlgoliaConfig::INDEXED_PRODUCT_FIELD_NAME_NAME, $suggestionsSearchResponseTransfer->getMatches());
        $this->assertArrayHasKey(AlgoliaConfig::INDEXED_PRODUCT_FIELD_NAME_ABSTRACT_NAME, $suggestionsSearchResponseTransfer->getMatches());

        $skus = array_column($suggestionsSearchResponseTransfer->getMatchedItems(), 'sku');
        foreach ($suggestionsSearchResponseTransfer->getMatches() as $match) {
            $this->assertEmpty(array_diff($match, $skus));
        }
    }

    /**
     * @return void
     */
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

        $this->tester->ensureFactoryConfigIsMocked();

        $searchRequestTransfer = $this->tester->haveSearchRequestTransfer();

        // Act
        $suggestionsSearchResponseTransfer = $this->tester->getFacade()->searchSuggestions($searchRequestTransfer);

        // Assert
        $this->assertEmpty($suggestionsSearchResponseTransfer->getCompletions());
    }

    /**
     * @return void
     */
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

        $this->tester->ensureFactoryConfigIsMocked();

        $searchRequestTransfer = $this->tester->haveSearchRequestTransfer();

        // Assert
        $this->expectException(Exception::class);

        // Act
        $suggestionsSearchResponseTransfer = $this->tester->getFacade()->searchSuggestions($searchRequestTransfer);
    }
}
