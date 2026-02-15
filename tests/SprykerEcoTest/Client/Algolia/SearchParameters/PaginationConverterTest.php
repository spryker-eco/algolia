<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Client\Algolia\Api\SearchParameters;

use Codeception\Test\Unit;
use Generated\Shared\DataBuilder\PaginationEntryBuilder;
use Generated\Shared\Transfer\PaginationEntryTransfer;
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
 * @group PaginationConverterTest
 * Add your own group annotations below this line
 */
class PaginationConverterTest extends Unit
{
    /**
     * @var \SprykerEcoTest\Client\Algolia\AlgoliaClientTester
     */
    protected AlgoliaClientTester $tester;

    /**
     * @return void
     */
    public function testConvertPaginationTransferToAlgoliaPaginationArrayWillReturnDefaultValuesForEmptyPaginationEntry()
    {
        // Arrange
        $paginationConverter = $this->tester->getFactory()->createPaginationConverter();
        $paginationEntryTransfer = (new PaginationEntryBuilder([
            PaginationEntryTransfer::PAGE => null,
            PaginationEntryTransfer::HITS_PER_PAGE => null,
            PaginationEntryTransfer::OFFSET => null,
            PaginationEntryTransfer::LENGTH => null,
        ]))
        ->build();

        // Act
        $pagination = $paginationConverter->convertPaginationTransferToAlgoliaPaginationArray($paginationEntryTransfer);

        // Assert
        $this->assertEquals(0, $pagination['offset']);
        $this->assertEquals(20, $pagination['length']);
    }

    /**
     * @return void
     */
    public function testConvertPaginationTransferToAlgoliaPaginationArrayWillReturnCorrectValuesWhenPageIsSet()
    {
        // Arrange
        $paginationConverter = $this->tester->getFactory()->createPaginationConverter();
        $paginationEntryTransfer = (new PaginationEntryBuilder([
            PaginationEntryTransfer::PAGE => 1,
            PaginationEntryTransfer::HITS_PER_PAGE => null,
        ]))
        ->build();

        // Act
        $pagination = $paginationConverter->convertPaginationTransferToAlgoliaPaginationArray($paginationEntryTransfer);

        // Assert
        $this->assertEquals(0, $pagination['page']);
        $this->assertEquals(20, $pagination['hitsPerPage']);
    }
}
