<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerEcoTest\Client\Algolia\Api\SearchParameters;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\AlgoliaConfigTransfer;
use Generated\Shared\Transfer\PaginationEntryTransfer;
use Generated\Shared\Transfer\SearchRequestTransfer;
use PHPUnit\Framework\MockObject\MockObject;
use SprykerEco\Client\Algolia\AlgoliaConfig;
use SprykerEco\Client\Algolia\Api\SearchParameters\Filter\FilterConverterInterface;
use SprykerEco\Client\Algolia\Api\SearchParameters\Pagination\PaginationConverterInterface;
use SprykerEco\Client\Algolia\Api\SearchParameters\SearchParametersResolver;
use SprykerEcoTest\Client\Algolia\AlgoliaClientTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerEcoTest
 * @group Client
 * @group Algolia
 * @group Business
 * @group Api
 * @group SearchParameters
 * @group SearchParametersResolverTest
 * Add your own group annotations below this line
 */
class SearchParametersResolverTest extends Unit
{
    /**
     * @var string
     */
    protected const TEST_FILTERS = 'category:electronics AND brand:apple';

    /**
     * @var array<string, mixed>
     */
    protected const TEST_PAGINATION = [
        'offset' => 0,
        'length' => 20,
    ];

    /**
     * @var string
     */
    protected const TEST_USER_TOKEN = 'test-user-token-123';

    /**
     * @var string
     */
    protected const TEST_USER_IP = '192.168.1.1';

    protected AlgoliaClientTester $tester;

    public function testGetSearchParametersReturnsBasicParametersWithoutPersonalization(): void
    {
        // Arrange
        $searchRequestTransfer = (new SearchRequestTransfer())
            ->setPagination(new PaginationEntryTransfer())
            ->setSourceIdentifier('cms-page');

        $filterConverterMock = $this->createFilterConverterMock();
        $filterConverterMock
            ->expects($this->once())
            ->method('convertFacetCollectionTransferToAlgoliaFiltersString')
            ->with($searchRequestTransfer)
            ->willReturn(static::TEST_FILTERS);

        $paginationConverterMock = $this->createPaginationConverterMock();
        $paginationConverterMock
            ->expects($this->once())
            ->method('convertPaginationTransferToAlgoliaPaginationArray')
            ->with($searchRequestTransfer->getPagination())
            ->willReturn(static::TEST_PAGINATION);

        $searchParametersResolver = new SearchParametersResolver($filterConverterMock, $paginationConverterMock, []);

        // Act
        $result = $searchParametersResolver->getSearchParameters($searchRequestTransfer, new AlgoliaConfigTransfer());

        // Assert
        $searchParams = $result->getSearchParams();
        $this->assertEquals(static::TEST_FILTERS, $searchParams['filters']);
        $this->assertEquals(['*'], $searchParams['facets']);
        $this->assertTrue($searchParams['clickAnalytics']);
        $this->assertEquals(static::TEST_PAGINATION['offset'], $searchParams['offset']);
        $this->assertEquals(static::TEST_PAGINATION['length'], $searchParams['length']);
        $this->assertArrayNotHasKey('enablePersonalization', $searchParams);
        $this->assertArrayNotHasKey('userToken', $searchParams);
        $this->assertEmpty($result->getRequestOptions());
    }

    public function testGetSearchParametersIncludesPersonalizationWhenEnabled(): void
    {
        // Arrange
        $algoliaConfigTransfer = (new AlgoliaConfigTransfer())
            ->setEnabledFeatures([AlgoliaConfig::FEATURE_PERSONALIZATION]);

        $searchRequestTransfer = (new SearchRequestTransfer())
            ->setPagination(new PaginationEntryTransfer())
            ->setUserToken(static::TEST_USER_TOKEN)
            ->setSourceIdentifier('cms-page');

        $filterConverterMock = $this->createFilterConverterMock();
        $filterConverterMock
            ->method('convertFacetCollectionTransferToAlgoliaFiltersString')
            ->willReturn(static::TEST_FILTERS);

        $paginationConverterMock = $this->createPaginationConverterMock();
        $paginationConverterMock
            ->method('convertPaginationTransferToAlgoliaPaginationArray')
            ->willReturn(static::TEST_PAGINATION);

        $searchParametersResolver = new SearchParametersResolver($filterConverterMock, $paginationConverterMock, []);

        // Act
        $result = $searchParametersResolver->getSearchParameters($searchRequestTransfer, $algoliaConfigTransfer);

        // Assert
        $searchParams = $result->getSearchParams();
        $this->assertTrue($searchParams['enablePersonalization']);
        $this->assertEquals(static::TEST_USER_TOKEN, $searchParams['userToken']);
    }

    public function testGetSearchParametersIncludesUserIpInRequestOptions(): void
    {
        // Arrange
        $searchRequestTransfer = (new SearchRequestTransfer())
            ->setPagination(new PaginationEntryTransfer())
            ->setUserIp(static::TEST_USER_IP)
            ->setSourceIdentifier('cms-page');

        $filterConverterMock = $this->createFilterConverterMock();
        $filterConverterMock
            ->method('convertFacetCollectionTransferToAlgoliaFiltersString')
            ->willReturn(static::TEST_FILTERS);

        $paginationConverterMock = $this->createPaginationConverterMock();
        $paginationConverterMock
            ->method('convertPaginationTransferToAlgoliaPaginationArray')
            ->willReturn(static::TEST_PAGINATION);

        $searchParametersResolver = new SearchParametersResolver($filterConverterMock, $paginationConverterMock, []);

        // Act
        $result = $searchParametersResolver->getSearchParameters($searchRequestTransfer, new AlgoliaConfigTransfer());

        // Assert
        $requestOptions = $result->getRequestOptions();
        $this->assertEquals(static::TEST_USER_IP, $requestOptions['headers']['X-Forwarded-For']);
        $this->assertArrayNotHasKey('X-Forwarded-For', $result->getSearchParams());
    }

    public function testGetSearchParametersIncludesBothPersonalizationAndUserIp(): void
    {
        // Arrange
        $algoliaConfigTransfer = (new AlgoliaConfigTransfer())
            ->setEnabledFeatures([AlgoliaConfig::FEATURE_PERSONALIZATION]);

        $searchRequestTransfer = (new SearchRequestTransfer())
            ->setPagination(new PaginationEntryTransfer())
            ->setUserToken(static::TEST_USER_TOKEN)
            ->setUserIp(static::TEST_USER_IP)
            ->setSourceIdentifier('cms-page');

        $filterConverterMock = $this->createFilterConverterMock();
        $filterConverterMock
            ->method('convertFacetCollectionTransferToAlgoliaFiltersString')
            ->willReturn(static::TEST_FILTERS);

        $paginationConverterMock = $this->createPaginationConverterMock();
        $paginationConverterMock
            ->method('convertPaginationTransferToAlgoliaPaginationArray')
            ->willReturn(static::TEST_PAGINATION);

        $searchParametersResolver = new SearchParametersResolver($filterConverterMock, $paginationConverterMock, []);

        // Act
        $result = $searchParametersResolver->getSearchParameters($searchRequestTransfer, $algoliaConfigTransfer);

        // Assert
        $searchParams = $result->getSearchParams();
        $this->assertTrue($searchParams['enablePersonalization']);
        $this->assertEquals(static::TEST_USER_TOKEN, $searchParams['userToken']);

        $requestOptions = $result->getRequestOptions();
        $this->assertEquals(static::TEST_USER_IP, $requestOptions['headers']['X-Forwarded-For']);
    }

    public function testGetSearchParametersExcludesPersonalizationWhenFeatureNotEnabled(): void
    {
        // Arrange
        $algoliaConfigTransfer = (new AlgoliaConfigTransfer())
            ->setEnabledFeatures([]); // No personalization feature

        $searchRequestTransfer = (new SearchRequestTransfer())
            ->setPagination(new PaginationEntryTransfer())
            ->setUserToken(static::TEST_USER_TOKEN)
            ->setSourceIdentifier('cms-page');

        $filterConverterMock = $this->createFilterConverterMock();
        $filterConverterMock
            ->method('convertFacetCollectionTransferToAlgoliaFiltersString')
            ->willReturn(static::TEST_FILTERS);

        $paginationConverterMock = $this->createPaginationConverterMock();
        $paginationConverterMock
            ->method('convertPaginationTransferToAlgoliaPaginationArray')
            ->willReturn(static::TEST_PAGINATION);

        $searchParametersResolver = new SearchParametersResolver($filterConverterMock, $paginationConverterMock, []);

        // Act
        $result = $searchParametersResolver->getSearchParameters($searchRequestTransfer, $algoliaConfigTransfer);

        // Assert
        $searchParams = $result->getSearchParams();
        $this->assertArrayNotHasKey('enablePersonalization', $searchParams);
        $this->assertArrayNotHasKey('userToken', $searchParams);
    }

    public function testGetSearchParametersExcludesPersonalizationWhenUserTokenNotProvided(): void
    {
        // Arrange
        $algoliaConfigTransfer = (new AlgoliaConfigTransfer())
            ->setEnabledFeatures([AlgoliaConfig::FEATURE_PERSONALIZATION]);

        $searchRequestTransfer = (new SearchRequestTransfer())
            ->setPagination(new PaginationEntryTransfer())
            ->setSourceIdentifier('cms-page');
            // No userToken set

        $filterConverterMock = $this->createFilterConverterMock();
        $filterConverterMock
            ->method('convertFacetCollectionTransferToAlgoliaFiltersString')
            ->willReturn(static::TEST_FILTERS);

        $paginationConverterMock = $this->createPaginationConverterMock();
        $paginationConverterMock
            ->method('convertPaginationTransferToAlgoliaPaginationArray')
            ->willReturn(static::TEST_PAGINATION);

        $searchParametersResolver = new SearchParametersResolver($filterConverterMock, $paginationConverterMock, []);

        // Act
        $result = $searchParametersResolver->getSearchParameters($searchRequestTransfer, $algoliaConfigTransfer);

        // Assert
        $searchParams = $result->getSearchParams();
        $this->assertArrayNotHasKey('enablePersonalization', $searchParams);
        $this->assertArrayNotHasKey('userToken', $searchParams);
    }

    public function testGetSearchParametersWithEmptyFiltersAndPagination(): void
    {
        // Arrange
        $searchRequestTransfer = (new SearchRequestTransfer())
            ->setPagination(new PaginationEntryTransfer())
            ->setSourceIdentifier('cms-page');

        $filterConverterMock = $this->createFilterConverterMock();
        $filterConverterMock
            ->method('convertFacetCollectionTransferToAlgoliaFiltersString')
            ->willReturn('');

        $paginationConverterMock = $this->createPaginationConverterMock();
        $paginationConverterMock
            ->method('convertPaginationTransferToAlgoliaPaginationArray')
            ->willReturn([]);

        $searchParametersResolver = new SearchParametersResolver($filterConverterMock, $paginationConverterMock, []);

        // Act
        $result = $searchParametersResolver->getSearchParameters($searchRequestTransfer, new AlgoliaConfigTransfer());

        // Assert
        $searchParams = $result->getSearchParams();
        $this->assertEquals('', $searchParams['filters']);
        $this->assertEquals(['*'], $searchParams['facets']);
        $this->assertTrue($searchParams['clickAnalytics']);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\SprykerEco\Client\Algolia\Api\SearchParameters\Filter\FilterConverterInterface
     */
    protected function createFilterConverterMock(): MockObject|FilterConverterInterface
    {
        return $this->createMock(FilterConverterInterface::class);
    }

    protected function createPaginationConverterMock(): MockObject|PaginationConverterInterface
    {
        return $this->createMock(PaginationConverterInterface::class);
    }
}
