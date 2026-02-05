<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Zed\Algolia\Business;

use Algolia\AlgoliaSearch\Algolia;
use Algolia\AlgoliaSearch\Exceptions\UnreachableException;
use ArrayObject;
use Codeception\Test\Unit;
use Generated\Shared\Transfer\AlgoliaResponseTransfer;
use Generated\Shared\Transfer\ProductConcreteTransfer;
use Generated\Shared\Transfer\ProductDeletedTransfer;

/**
 * Auto-generated group annotations
 *
 * @group SprykerEcoTest
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
        $this->markTestSkipped('mocked listIndices for some reason returns empty array.');

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
}
