<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Zed\Algolia\Business\Indexer;

use Codeception\Test\Unit;
use Exception;
use Generated\Shared\Transfer\CmsPagePublishedTransfer;
use Generated\Shared\Transfer\CmsPageTransfer;
use SprykerEco\Zed\Algolia\Business\Indexer\CmsPageIndexer;
use SprykerEco\Zed\Algolia\Business\IndexResolver\IndexNameResolver;
use SprykerEco\Zed\Algolia\Business\Mapper\CmsPageMapperInterface;

/**
 * Auto-generated group annotations
 *
 * @group PyzTest
 * @group Zed
 * @group Algolia
 * @group Business
 * @group Indexer
 * @group CmsPageIndexerTest
 * Add your own group annotations below this line
 */
class CmsPageIndexerTest extends Unit
{
    /**
     * @var string
     */
    protected const TEST_LOCALE = 'en_US';

    /**
     * @var string
     */
    protected const TEST_TENANT_IDENTIFIER = 'test-tenant';

    /**
     * @var string
     */
    protected const TEST_INDEX_NAME = 'test-cms-index';

    /**
     * @var \SprykerEcoTest\Zed\Algolia\AlgoliaBusinessTester
     */
    protected $tester;

    /**
     * @return void
     */
    public function testBuildIndexDataReturnsCorrectStructure(): void
    {
        // Arrange
        $cmsPageMapperMock = $this->createMock(CmsPageMapperInterface::class);
        $indexNameResolverMock = $this->createMock(IndexNameResolver::class);

        $indexNameResolverMock
            ->method('resolveCmsPageIndexName')
            ->with(static::TEST_LOCALE, static::TEST_TENANT_IDENTIFIER)
            ->willReturn(static::TEST_INDEX_NAME);

        $expectedMappedData = [
            'objectID' => 1,
            'title' => 'Test Page',
            'content' => 'Test content',
            'url' => '/test-page',
        ];

        $cmsPageMapperMock
            ->method('mapCmsPageDataToAlgoliaData')
            ->willReturn($expectedMappedData);

        $cmsPageIndexer = new CmsPageIndexer($cmsPageMapperMock, $indexNameResolverMock);

        $cmsPagePublishedTransfer = new CmsPagePublishedTransfer();
        $cmsPageTransfer = new CmsPageTransfer();
        $flattenedLocaleCmsPageData = ['title' => 'Test Page'];

        // Act
        $result = $cmsPageIndexer->buildIndexData(
            $cmsPagePublishedTransfer,
            static::TEST_LOCALE,
            static::TEST_TENANT_IDENTIFIER,
            $cmsPageTransfer,
            $flattenedLocaleCmsPageData,
        );

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('indexName', $result);
        $this->assertArrayHasKey('locale', $result);
        $this->assertArrayHasKey('tenantIdentifier', $result);
        $this->assertArrayHasKey('data', $result);

        $this->assertEquals(static::TEST_INDEX_NAME, $result['indexName']);
        $this->assertEquals(static::TEST_LOCALE, $result['locale']);
        $this->assertEquals(static::TEST_TENANT_IDENTIFIER, $result['tenantIdentifier']);
        $this->assertEquals($expectedMappedData, $result['data']);
    }

    /**
     * @return void
     */
    public function testBuildIndexDataReturnsEmptyArrayOnMapperException(): void
    {
        // Arrange
        $cmsPageMapperMock = $this->createMock(CmsPageMapperInterface::class);
        $indexNameResolverMock = $this->createMock(IndexNameResolver::class);

        $indexNameResolverMock
            ->method('resolveCmsPageIndexName')
            ->willReturn(static::TEST_INDEX_NAME);

        $cmsPageMapperMock
            ->method('mapCmsPageDataToAlgoliaData')
            ->willThrowException(new Exception('Mapping failed'));

        $cmsPageIndexer = new CmsPageIndexer($cmsPageMapperMock, $indexNameResolverMock);

        $cmsPagePublishedTransfer = new CmsPagePublishedTransfer();
        $cmsPageTransfer = new CmsPageTransfer();
        $flattenedLocaleCmsPageData = [];

        // Act
        $result = $cmsPageIndexer->buildIndexData(
            $cmsPagePublishedTransfer,
            static::TEST_LOCALE,
            static::TEST_TENANT_IDENTIFIER,
            $cmsPageTransfer,
            $flattenedLocaleCmsPageData,
        );

        // Assert
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * @return void
     */
    public function testBuildIndexDataCallsMapperWithCorrectParameters(): void
    {
        // Arrange
        $cmsPageMapperMock = $this->createMock(CmsPageMapperInterface::class);
        $indexNameResolverMock = $this->createMock(IndexNameResolver::class);

        $indexNameResolverMock
            ->method('resolveCmsPageIndexName')
            ->willReturn(static::TEST_INDEX_NAME);

        $cmsPagePublishedTransfer = new CmsPagePublishedTransfer();
        $cmsPageTransfer = new CmsPageTransfer();
        $flattenedLocaleCmsPageData = ['test' => 'data'];

        $cmsPageMapperMock
            ->expects($this->once())
            ->method('mapCmsPageDataToAlgoliaData')
            ->with(
                $cmsPagePublishedTransfer,
                static::TEST_LOCALE,
                $cmsPageTransfer,
                $flattenedLocaleCmsPageData,
            )
            ->willReturn(['mapped' => 'data']);

        $cmsPageIndexer = new CmsPageIndexer($cmsPageMapperMock, $indexNameResolverMock);

        // Act
        $cmsPageIndexer->buildIndexData(
            $cmsPagePublishedTransfer,
            static::TEST_LOCALE,
            static::TEST_TENANT_IDENTIFIER,
            $cmsPageTransfer,
            $flattenedLocaleCmsPageData,
        );

        // Assert - expectations are verified by PHPUnit automatically
    }

    /**
     * @return void
     */
    public function testBuildIndexDataCallsIndexNameResolverWithCorrectParameters(): void
    {
        // Arrange
        $cmsPageMapperMock = $this->createMock(CmsPageMapperInterface::class);
        $indexNameResolverMock = $this->createMock(IndexNameResolver::class);

        $indexNameResolverMock
            ->expects($this->once())
            ->method('resolveCmsPageIndexName')
            ->with(static::TEST_LOCALE, static::TEST_TENANT_IDENTIFIER)
            ->willReturn(static::TEST_INDEX_NAME);

        $cmsPageMapperMock
            ->method('mapCmsPageDataToAlgoliaData')
            ->willReturn(['test' => 'data']);

        $cmsPageIndexer = new CmsPageIndexer($cmsPageMapperMock, $indexNameResolverMock);

        $cmsPagePublishedTransfer = new CmsPagePublishedTransfer();
        $cmsPageTransfer = new CmsPageTransfer();
        $flattenedLocaleCmsPageData = [];

        // Act
        $cmsPageIndexer->buildIndexData(
            $cmsPagePublishedTransfer,
            static::TEST_LOCALE,
            static::TEST_TENANT_IDENTIFIER,
            $cmsPageTransfer,
            $flattenedLocaleCmsPageData,
        );

        // Assert - expectations are verified by PHPUnit automatically
    }

    /**
     * @return void
     */
    public function testBuildIndexDataHandlesNullMapperResult(): void
    {
        // Arrange
        $cmsPageMapperMock = $this->createMock(CmsPageMapperInterface::class);
        $indexNameResolverMock = $this->createMock(IndexNameResolver::class);

        $indexNameResolverMock
            ->method('resolveCmsPageIndexName')
            ->willReturn(static::TEST_INDEX_NAME);

        $cmsPageMapperMock
            ->method('mapCmsPageDataToAlgoliaData')
            ->willReturn(null);

        $cmsPageIndexer = new CmsPageIndexer($cmsPageMapperMock, $indexNameResolverMock);

        $cmsPagePublishedTransfer = new CmsPagePublishedTransfer();
        $cmsPageTransfer = new CmsPageTransfer();
        $flattenedLocaleCmsPageData = [];

        // Act
        $result = $cmsPageIndexer->buildIndexData(
            $cmsPagePublishedTransfer,
            static::TEST_LOCALE,
            static::TEST_TENANT_IDENTIFIER,
            $cmsPageTransfer,
            $flattenedLocaleCmsPageData,
        );

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('indexName', $result);
        $this->assertArrayHasKey('locale', $result);
        $this->assertArrayHasKey('tenantIdentifier', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertNull($result['data']);
    }
}
