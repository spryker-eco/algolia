<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Zed\Algolia\Business\Api\Creator;

use Algolia\AlgoliaSearch\SearchClient;
use Codeception\Test\Unit;
use Generated\Shared\Transfer\AlgoliaConfigTransfer;

/**
 * Auto-generated group annotations
 *
 * @group SprykerEcoTest
 * @group Zed
 * @group Algolia
 * @group Business
 * @group Api
 * @group Creator
 * @group SearchClientCreatorTest
 * Add your own group annotations below this line
 */
class SearchClientCreatorTest extends Unit
{
    /**
     * @var \SprykerEcoTest\Zed\Algolia\AlgoliaBusinessTester
     */
    protected $tester;

    public function testCreateSearchClient(): void
    {
        // Arrange
        $algoliaApiCredentialsTransfer = $this->tester->haveAlgoliaApiCredentialsTransfer();
        $searchClientCreator = $this->tester->getFactory()->createSearchClientCreator();

        // Act
        $searchClient = $searchClientCreator->createSearchClientWithCredentials($algoliaApiCredentialsTransfer);

        // Assert
        $this->assertInstanceOf(
            SearchClient::class,
            $searchClient,
        );
    }

    public function testCreateSearchClientByStoreReferenceSuccessfully(): void
    {
        // Arrange
        $searchClientCreator = $this->tester->getFactory()->createSearchClientCreator();

        // Act
        $searchClient = $searchClientCreator
            ->createSearchClientFromConfig((new AlgoliaConfigTransfer()));

        // Assert
        $this->assertInstanceOf(
            SearchClient::class,
            $searchClient,
        );
    }
}
