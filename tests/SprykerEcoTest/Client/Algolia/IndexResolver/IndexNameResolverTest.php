<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Client\Algolia\IndexResolver;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\FacetCollectionTransfer;
use Generated\Shared\Transfer\FacetEntryTransfer;
use Generated\Shared\Transfer\FacetParametersTransfer;
use Generated\Shared\Transfer\SortingEntryTransfer;
use SprykerEco\Client\Algolia\AlgoliaConfig;
use SprykerEco\Client\Algolia\IndexResolver\IndexNameResolver;
use SprykerEco\Shared\Algolia\Enum\AlgoliaEntityNameEnum;
use SprykerEcoTest\Client\Algolia\AlgoliaClientTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerEcoTest
 * @group Client
 * @group Algolia
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
    protected AlgoliaClientTester $tester;

    public function testGetIndexReplicaNameForSortingWithAscendingDirection(): void
    {
        // Arrange
        $sortingEntryTransfer = (new SortingEntryTransfer())
            ->setField('name')
            ->setDirection('asc');

        // Act
        $result = $this->createIndexNameResolver()->getIndexReplicaNameForSorting(
            static::TEST_INDEX_NAME,
            $sortingEntryTransfer,
            new FacetCollectionTransfer(),
            AlgoliaEntityNameEnum::PRODUCT->value,
        );

        // Assert
        $this->assertSame(static::TEST_INDEX_NAME . '-asc-name', $result);
    }

    public function testGetIndexReplicaNameForSortingWithDescendingDirection(): void
    {
        // Arrange
        $sortingEntryTransfer = (new SortingEntryTransfer())
            ->setField('name')
            ->setDirection('desc');

        // Act
        $result = $this->createIndexNameResolver()->getIndexReplicaNameForSorting(
            static::TEST_INDEX_NAME,
            $sortingEntryTransfer,
            new FacetCollectionTransfer(),
            AlgoliaEntityNameEnum::PRODUCT->value,
        );

        // Assert
        $this->assertSame(static::TEST_INDEX_NAME . '-desc-name', $result);
    }

    public function testGetIndexReplicaNameForSortingWithPriceField(): void
    {
        // Arrange
        $sortingEntryTransfer = (new SortingEntryTransfer())
            ->setField('price')
            ->setDirection('asc');

        $facetCollectionTransfer = (new FacetCollectionTransfer())
                ->addFacet(
                    'price_mode',
                    (new FacetEntryTransfer())
                        ->setParameters((new FacetParametersTransfer())->setValues(['gross'])),
                )
                ->addFacet(
                    'currency',
                    (new FacetEntryTransfer())
                        ->setParameters((new FacetParametersTransfer())->setValues(['eur'])),
                );

        // Act
        $result = $this->createIndexNameResolver()->getIndexReplicaNameForSorting(
            static::TEST_INDEX_NAME,
            $sortingEntryTransfer,
            $facetCollectionTransfer,
            AlgoliaEntityNameEnum::PRODUCT->value,
        );

        // Assert
        $this->assertStringContainsString('prices.eur', $result);
        $this->assertStringContainsString('asc', $result);
    }

    public function testGetIndexReplicaNameForSortingAppliesMappingWhenConfigured(): void
    {
        // Arrange
        $sortingEntryTransfer = (new SortingEntryTransfer())
            ->setField('name')
            ->setDirection('asc');

        $algoliaConfigMock = $this->createMock(AlgoliaConfig::class);
        $algoliaConfigMock->method('getProductSortingParamToAttributeMapping')->willReturn(['name' => 'abstract_name']);

        // Act
        $result = (new IndexNameResolver($algoliaConfigMock))->getIndexReplicaNameForSorting(
            static::TEST_INDEX_NAME,
            $sortingEntryTransfer,
            new FacetCollectionTransfer(),
            AlgoliaEntityNameEnum::PRODUCT->value,
        );

        // Assert
        $this->assertSame(static::TEST_INDEX_NAME . '-asc-abstract_name', $result);
    }

    public function testGetIndexReplicaNameForSortingDoesNotApplyMappingForUnmappedField(): void
    {
        // Arrange
        $sortingEntryTransfer = (new SortingEntryTransfer())
            ->setField('rating')
            ->setDirection('desc');

        $algoliaConfigMock = $this->createMock(AlgoliaConfig::class);
        $algoliaConfigMock->method('getProductSortingParamToAttributeMapping')->willReturn(['name' => 'abstract_name']);

        // Act
        $result = (new IndexNameResolver($algoliaConfigMock))->getIndexReplicaNameForSorting(
            static::TEST_INDEX_NAME,
            $sortingEntryTransfer,
            new FacetCollectionTransfer(),
            AlgoliaEntityNameEnum::PRODUCT->value,
        );

        // Assert
        $this->assertSame(static::TEST_INDEX_NAME . '-desc-rating', $result);
    }

    protected function createIndexNameResolver(): IndexNameResolver
    {
        return new IndexNameResolver($this->createMock(AlgoliaConfig::class));
    }
}
