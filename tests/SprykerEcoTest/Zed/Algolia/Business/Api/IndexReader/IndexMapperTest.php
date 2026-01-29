<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerEcoTest\Zed\Algolia\Business\Api\IndexReader;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\AlgoliaIndexTransfer;
use Generated\Shared\Transfer\AlgoliaIndicesCollectionTransfer;
use SprykerEco\Zed\Algolia\Business\Api\IndexReader\IndexMapper;
use SprykerEcoTest\Zed\Algolia\AlgoliaBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group PyzTest
 * @group Zed
 * @group Algolia
 * @group Business
 * @group Api
 * @group IndexReader
 * @group IndexMapperTest
 * Add your own group annotations below this line
 */
class IndexMapperTest extends Unit
{
    /**
     * @var \SprykerEcoTest\Zed\Algolia\AlgoliaBusinessTester
     */
    protected AlgoliaBusinessTester $tester;

    /**
     * @return void
     */
    public function testMapAlgoliaListIndicesResponseToAlgoliaIndicesCollectionTransferMapsIndicesCorrectly(): void
    {
        // Arrange
        $algoliaResponse = [
            'items' => [
                [
                    'name' => 'test-index-1',
                    'replicas' => ['test-index-1_replica1', 'test-index-1_replica2'],
                ],
                [
                    'name' => 'test-index-2',
                    // No replicas - this is a replica itself
                ],
            ],
        ];

        $algoliaIndicesCollectionTransfer = new AlgoliaIndicesCollectionTransfer();
        $indexMapper = new IndexMapper();

        // Act
        $result = $indexMapper->mapAlgoliaListIndicesResponseToAlgoliaIndicesCollectionTransfer(
            $algoliaResponse,
            $algoliaIndicesCollectionTransfer,
        );

        // Assert
        $this->assertInstanceOf(AlgoliaIndicesCollectionTransfer::class, $result);
        $this->assertCount(2, $result->getIndices());

        $indices = $result->getIndices()->getArrayCopy();

        // First index (primary with replicas)
        /** @var \Generated\Shared\Transfer\AlgoliaIndexTransfer $firstIndex */
        $firstIndex = $indices[0];
        $this->assertInstanceOf(AlgoliaIndexTransfer::class, $firstIndex);
        $this->assertEquals('test-index-1', $firstIndex->getName());
        $this->assertTrue($firstIndex->getIsPrimary()); // Has replicas, so it's primary

        // Second index (replica without replicas)
        /** @var \Generated\Shared\Transfer\AlgoliaIndexTransfer $secondIndex */
        $secondIndex = $indices[1];
        $this->assertInstanceOf(AlgoliaIndexTransfer::class, $secondIndex);
        $this->assertEquals('test-index-2', $secondIndex->getName());
        $this->assertFalse($secondIndex->getIsPrimary()); // No replicas, so it's not primary
    }

    /**
     * @return void
     */
    public function testMapAlgoliaListIndicesResponseToAlgoliaIndicesCollectionTransferHandlesPartialData(): void
    {
        // Arrange
        $algoliaResponse = [
            'items' => [
                [
                    'name' => 'minimal-index',
                    // Missing other optional fields
                ],
            ],
        ];

        $algoliaIndicesCollectionTransfer = new AlgoliaIndicesCollectionTransfer();
        $indexMapper = new IndexMapper();

        // Act
        $result = $indexMapper->mapAlgoliaListIndicesResponseToAlgoliaIndicesCollectionTransfer(
            $algoliaResponse,
            $algoliaIndicesCollectionTransfer,
        );

        // Assert
        $this->assertInstanceOf(AlgoliaIndicesCollectionTransfer::class, $result);
        $this->assertCount(1, $result->getIndices());

        $indices = $result->getIndices()->getArrayCopy();
        /** @var \Generated\Shared\Transfer\AlgoliaIndexTransfer $index */
        $index = $indices[0];
        $this->assertEquals('minimal-index', $index->getName());
        $this->assertFalse($index->getIsPrimary()); // No replicas field, so not primary
    }

    /**
     * @return void
     */
    public function testMapAlgoliaListIndicesResponseToAlgoliaIndicesCollectionTransferPreservesExistingIndices(): void
    {
        // Arrange
        $existingIndex = (new AlgoliaIndexTransfer())
            ->setName('existing-index')
            ->setIsPrimary(true);

        $algoliaIndicesCollectionTransfer = (new AlgoliaIndicesCollectionTransfer())
            ->addIndex($existingIndex);

        $algoliaResponse = [
            'items' => [
                [
                    'name' => 'new-index',
                ],
            ],
        ];

        $indexMapper = new IndexMapper();

        // Act
        $result = $indexMapper->mapAlgoliaListIndicesResponseToAlgoliaIndicesCollectionTransfer(
            $algoliaResponse,
            $algoliaIndicesCollectionTransfer,
        );

        // Assert
        $this->assertInstanceOf(AlgoliaIndicesCollectionTransfer::class, $result);
        $this->assertCount(2, $result->getIndices()); // Existing + new

        $indices = $result->getIndices()->getArrayCopy();

        // Check existing index is preserved
        /** @var \Generated\Shared\Transfer\AlgoliaIndexTransfer $firstIndex */
        $firstIndex = $indices[0];
        $this->assertEquals('existing-index', $firstIndex->getName());
        $this->assertTrue($firstIndex->getIsPrimary());

        // Check new index is added
        /** @var \Generated\Shared\Transfer\AlgoliaIndexTransfer $secondIndex */
        $secondIndex = $indices[1];
        $this->assertEquals('new-index', $secondIndex->getName());
        $this->assertFalse($secondIndex->getIsPrimary());
    }
}
