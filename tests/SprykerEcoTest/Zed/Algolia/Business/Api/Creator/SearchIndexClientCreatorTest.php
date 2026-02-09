<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Zed\Algolia\Business\Api\Creator;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\AlgoliaConfigTransfer;
use Generated\Shared\Transfer\IndexConfigurationTransfer;
use SprykerEco\Zed\Algolia\Business\Api\Creator\SearchIndexClientCreator;

/**
 * Auto-generated group annotations
 *
 * @group SprykerEcoTest
 * @group Zed
 * @group Algolia
 * @group Business
 * @group Api
 * @group Creator
 * @group SearchIndexClientCreatorTest
 * Add your own group annotations below this line
 */
class SearchIndexClientCreatorTest extends Unit
{
    /**
     * @var string
     */
    protected const INDEX_NAME_TEST = 'test-index';

    /**
     * @var \SprykerEcoTest\Zed\Algolia\AlgoliaBusinessTester
     */
    protected $tester;

    public function testCreateSearchIndexApiClientWillCallIndexConfiguratorIfIndexDoesNotExist(): void
    {
        // Arrange
        $searchClientMock = $this->tester->createSearchClientMockForNonExistingIndex(static::INDEX_NAME_TEST);

        $indexConfiguratorMock = $this->tester->createIndexConfiguratorMock($this->once());

        // Act
        $searchIndexClientCreator = new SearchIndexClientCreator($indexConfiguratorMock);

        $searchIndexClient = $searchIndexClientCreator->createSearchIndexApiClient(
            $searchClientMock,
            (new IndexConfigurationTransfer())
                ->setIndexName(static::INDEX_NAME_TEST)
                ->setLocale('')
                ->setAlgoliaConfig($this->tester->haveAlgoliaConfigTransfer([AlgoliaConfigTransfer::IS_PRODUCT_PRICE_SYNCED => true])),
        );

        // Assert
        $this->assertNotNull($searchIndexClient);
    }

    public function testCreateSearchIndexApiClientWillNotCallIndexConfiguratorIfIndexDoesExist(): void
    {
        // Arrange
        $searchClientMock = $this->tester->createSearchClientMockForExistingIndex(static::INDEX_NAME_TEST);

        $indexConfiguratorMock = $this->tester->createIndexConfiguratorMock($this->never());
        // Act
        $searchIndexClientCreator = new SearchIndexClientCreator($indexConfiguratorMock);

        $searchIndexClient = $searchIndexClientCreator->createSearchIndexApiClient(
            $searchClientMock,
            (new IndexConfigurationTransfer())
                ->setIndexName(static::INDEX_NAME_TEST)
                ->setLocale('')
                ->setAlgoliaConfig(
                    $this->tester->haveAlgoliaConfigTransfer([AlgoliaConfigTransfer::IS_PRODUCT_PRICE_SYNCED => false]),
                ),
        );
        // Assert

        $this->assertNotNull($searchIndexClient);
    }
}
