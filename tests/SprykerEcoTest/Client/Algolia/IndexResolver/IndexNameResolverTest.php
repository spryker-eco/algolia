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
use SprykerEco\Client\Algolia\IndexResolver\IndexNameResolver;
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

    /**
     * @return void
     */
    public function testGetIndexReplicaNameForSortingWithAscendingDirection(): void
    {
        // Arrange
        $sortingEntryTransfer = (new SortingEntryTransfer())
            ->setField('name')
            ->setDirection('asc');

        $facetCollectionTransfer = new FacetCollectionTransfer();

        // Act
        $result = (new IndexNameResolver())->getIndexReplicaNameForSorting(
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
        $sortingEntryTransfer = (new SortingEntryTransfer())
            ->setField('name')
            ->setDirection('desc');

        $facetCollectionTransfer = new FacetCollectionTransfer();

        // Act
        $result = (new IndexNameResolver())->getIndexReplicaNameForSorting(
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
        $result = (new IndexNameResolver())->getIndexReplicaNameForSorting(
            static::TEST_INDEX_NAME,
            $sortingEntryTransfer,
            $facetCollectionTransfer,
        );

        // Assert
        $this->assertIsString($result);
        $this->assertStringContainsString(static::TEST_INDEX_NAME, $result);
        $this->assertStringContainsString('prices.eur', $result);
        $this->assertStringContainsString('asc', $result);
    }
}
