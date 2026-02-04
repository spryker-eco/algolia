<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Zed\Algolia\Business\IndexResolver;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\AlgoliaIndexTransfer;
use Generated\Shared\Transfer\AlgoliaIndicesCollectionTransfer;
use Generated\Shared\Transfer\FacetCollectionTransfer;
use Generated\Shared\Transfer\SortingEntryTransfer;
use SprykerEco\Zed\Algolia\AlgoliaConfig;
use SprykerEco\Zed\Algolia\Business\IndexResolver\IndexNameResolver;
use SprykerEcoTest\Zed\Algolia\AlgoliaBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group PyzTest
 * @group Zed
 * @group Algolia
 * @group Business
 * @group IndexResolver
 * @group IndexNameResolverTest
 * Add your own group annotations below this line
 */
class IndexNameResolverTest extends Unit
{
    /**
     * @var string
     */
    protected const TEST_TENANT_IDENTIFIER = 'test-tenant';

    /**
     * @var string
     */
    protected const TEST_STORE_NAME = 'DE';

    /**
     * @var string
     */
    protected const TEST_LOCALE = 'de_DE';

    /**
     * @var string
     */
    protected const TEST_INDEX_NAME = 'test-tenant-product-de-de_de';

    /**
     * @var \SprykerEcoTest\Zed\Algolia\AlgoliaBusinessTester
     */
    protected AlgoliaBusinessTester $tester;

    /**
     * @return void
     */
    public function testResolveProductIndexNameReturnsCorrectFormat(): void
    {
        // Arrange
        $indexNameResolver = new IndexNameResolver();

        // Act
        $result = $indexNameResolver->resolveProductIndexName(
            static::TEST_TENANT_IDENTIFIER,
            static::TEST_STORE_NAME,
            static::TEST_LOCALE,
        );

        // Assert
        $this->assertIsString($result);
        $this->assertStringContainsString(static::TEST_TENANT_IDENTIFIER, $result);
        $this->assertStringContainsString('product', $result);
        $this->assertStringContainsString(strtolower(static::TEST_STORE_NAME), $result);
        $this->assertStringContainsString(strtolower(static::TEST_LOCALE), $result);
        $this->assertEquals(strtolower($result), $result); // Should be lowercase
    }

    /**
     * @return void
     */
    public function testResolveSuggestionIndexNameFromProductIndexNameReturnsCorrectFormat(): void
    {
        // Arrange
        $indexNameResolver = new IndexNameResolver();

        // Act
        $result = $indexNameResolver->resolveProductsSuggestionIndexNameFromProductIndexName(static::TEST_INDEX_NAME);

        // Assert
        $this->assertIsString($result);
        $this->assertStringContainsString(static::TEST_INDEX_NAME, $result);
        $this->assertStringContainsString('suggestions', $result);
        $this->assertEquals(strtolower($result), $result); // Should be lowercase
    }

    /**
     * @return void
     */
    public function testResolveCmsPageIndexNameReturnsCorrectFormat(): void
    {
        // Arrange
        $indexNameResolver = new IndexNameResolver();

        // Act
        $result = $indexNameResolver->resolveCmsPageIndexName(
            static::TEST_LOCALE,
            static::TEST_TENANT_IDENTIFIER,
        );

        // Assert
        $this->assertIsString($result);
        $this->assertStringContainsString(static::TEST_TENANT_IDENTIFIER, $result);
        $this->assertStringContainsString('cms-page', $result);
        $this->assertStringContainsString(strtolower(static::TEST_LOCALE), $result);
        $this->assertEquals(strtolower($result), $result); // Should be lowercase
    }

    /**
     * @return void
     */
    public function testFilterIndicesByIndexNamePartsFiltersCorrectly(): void
    {
        // Arrange
        $indexNameResolver = new IndexNameResolver();

        $index1 = (new AlgoliaIndexTransfer())->setName('test-tenant-product-de-de_de');
        $index2 = (new AlgoliaIndexTransfer())->setName('other-tenant-product-de-de_de');
        $index3 = (new AlgoliaIndexTransfer())->setName('test-tenant-cms-page-de_de');

        $algoliaIndicesCollection = (new AlgoliaIndicesCollectionTransfer())
            ->addIndex($index1)
            ->addIndex($index2)
            ->addIndex($index3);

        // Act
        $result = $indexNameResolver->filterIndicesByIndexNameParts(
            $algoliaIndicesCollection,
            static::TEST_TENANT_IDENTIFIER,
        );

        // Assert
        $this->assertInstanceOf(AlgoliaIndicesCollectionTransfer::class, $result);
        $this->assertCount(2, $result->getIndices()); // Should filter out 'other-tenant'

        $resultIndices = $result->getIndices()->getArrayCopy();
        $this->assertEquals('test-tenant-product-de-de_de', $resultIndices[0]->getName());
        $this->assertEquals('test-tenant-cms-page-de_de', $resultIndices[1]->getName());
    }

    /**
     * @return void
     */
    public function testFilterIndicesByIndexNamePartsWithEntityNameFilter(): void
    {
        // Arrange
        $indexNameResolver = new IndexNameResolver();

        $index1 = (new AlgoliaIndexTransfer())->setName('test-tenant-product-de-de_de');
        $index2 = (new AlgoliaIndexTransfer())->setName('test-tenant-cms-page-de_de');

        $algoliaIndicesCollection = (new AlgoliaIndicesCollectionTransfer())
            ->addIndex($index1)
            ->addIndex($index2);

        // Act
        $result = $indexNameResolver->filterIndicesByIndexNameParts(
            $algoliaIndicesCollection,
            static::TEST_TENANT_IDENTIFIER,
            'product',
        );

        // Assert
        $this->assertInstanceOf(AlgoliaIndicesCollectionTransfer::class, $result);
        $this->assertCount(1, $result->getIndices()); // Should filter to only product indices

        $resultIndices = $result->getIndices()->getArrayCopy();
        $this->assertEquals('test-tenant-product-de-de_de', $resultIndices[0]->getName());
    }

    /**
     * @return void
     */
    public function testFilterIndicesByIndexNamePartsWithStoreNameFilter(): void
    {
        // Arrange
        $indexNameResolver = new IndexNameResolver();

        $index1 = (new AlgoliaIndexTransfer())->setName('test-tenant-product-de-de_de');
        $index2 = (new AlgoliaIndexTransfer())->setName('test-tenant-product-us-en_us');

        $algoliaIndicesCollection = (new AlgoliaIndicesCollectionTransfer())
            ->addIndex($index1)
            ->addIndex($index2);

        // Act
        $result = $indexNameResolver->filterIndicesByIndexNameParts(
            $algoliaIndicesCollection,
            static::TEST_TENANT_IDENTIFIER,
            null,
            'DE',
        );

        // Assert
        $this->assertInstanceOf(AlgoliaIndicesCollectionTransfer::class, $result);
        $this->assertCount(1, $result->getIndices()); // Should filter to only DE store indices

        $resultIndices = $result->getIndices()->getArrayCopy();
        $this->assertEquals('test-tenant-product-de-de_de', $resultIndices[0]->getName());
    }

    /**
     * @return void
     */
    public function testGetIndexReplicaNameForSortingWithAscendingDirection(): void
    {
        // Arrange
        $indexNameResolver = new IndexNameResolver();

        $sortingEntryTransfer = (new SortingEntryTransfer())
            ->setField('name')
            ->setDirection('asc');

        $facetCollectionTransfer = new FacetCollectionTransfer();

        // Act
        $result = $indexNameResolver->getIndexReplicaNameForSorting(
            static::TEST_INDEX_NAME,
            $sortingEntryTransfer,
            $facetCollectionTransfer,
        );

        // Assert
        $this->assertIsString($result);
        $this->assertStringContainsString(static::TEST_INDEX_NAME, $result);
        $this->assertStringContainsString('name', $result);
        $this->assertStringContainsString('asc', $result);
    }

    /**
     * @return void
     */
    public function testGetIndexReplicaNameForSortingWithDescendingDirection(): void
    {
        // Arrange
        $indexNameResolver = new IndexNameResolver();

        $sortingEntryTransfer = (new SortingEntryTransfer())
            ->setField('name')
            ->setDirection('desc');

        $facetCollectionTransfer = new FacetCollectionTransfer();

        // Act
        $result = $indexNameResolver->getIndexReplicaNameForSorting(
            static::TEST_INDEX_NAME,
            $sortingEntryTransfer,
            $facetCollectionTransfer,
        );

        // Assert
        $this->assertIsString($result);
        $this->assertStringContainsString(static::TEST_INDEX_NAME, $result);
        $this->assertStringContainsString('name', $result);
        $this->assertStringContainsString('desc', $result);
    }

    /**
     * @return void
     */
    public function testGetIndexReplicaNameForSortingWithPriceField(): void
    {
        // Arrange
        $indexNameResolver = new IndexNameResolver();

        $sortingEntryTransfer = (new SortingEntryTransfer())
            ->setField(AlgoliaConfig::FILTER_NAME_PRICE)
            ->setDirection('asc');

        $facetCollectionTransfer = new FacetCollectionTransfer();

        // Act
        $result = $indexNameResolver->getIndexReplicaNameForSorting(
            static::TEST_INDEX_NAME,
            $sortingEntryTransfer,
            $facetCollectionTransfer,
        );

        // Assert
        $this->assertIsString($result);
        $this->assertStringContainsString(static::TEST_INDEX_NAME, $result);
        $this->assertStringContainsString('price_eur', $result);
        $this->assertStringContainsString('asc', $result);
    }

    /**
     * @return void
     */
    public function testFilterIndicesByIndexNamePartsReturnsEmptyWhenNoMatches(): void
    {
        // Arrange
        $indexNameResolver = new IndexNameResolver();

        $index1 = (new AlgoliaIndexTransfer())->setName('other-tenant-product-de-de_de');
        $index2 = (new AlgoliaIndexTransfer())->setName('another-tenant-cms-page-de_de');

        $algoliaIndicesCollection = (new AlgoliaIndicesCollectionTransfer())
            ->addIndex($index1)
            ->addIndex($index2);

        // Act
        $result = $indexNameResolver->filterIndicesByIndexNameParts(
            $algoliaIndicesCollection,
            static::TEST_TENANT_IDENTIFIER,
        );

        // Assert
        $this->assertInstanceOf(AlgoliaIndicesCollectionTransfer::class, $result);
        $this->assertCount(0, $result->getIndices()); // Should return empty collection
    }
}
