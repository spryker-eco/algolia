<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Zed\Algolia\Business\Filter;

use ArrayObject;
use Codeception\Test\Unit;
use Generated\Shared\DataBuilder\MoneyValueBuilder;
use Generated\Shared\DataBuilder\PriceProductBuilder;
use Generated\Shared\DataBuilder\StoreBuilder;
use Generated\Shared\Transfer\AlgoliaConfigTransfer;
use Generated\Shared\Transfer\MoneyValueTransfer;
use Generated\Shared\Transfer\ProductConcreteTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use SprykerEco\Zed\Algolia\Business\Filter\ProductConcreteFilter;
use SprykerEcoTest\Zed\Algolia\AlgoliaBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerEcoTest
 * @group Zed
 * @group Algolia
 * @group Business
 * @group Filter
 * @group ConcreteProductFilterTest
 * Add your own group annotations below this line
 */
class ConcreteProductFilterTest extends Unit
{
    /**
     * @var \SprykerEcoTest\Zed\Algolia\AlgoliaBusinessTester
     */
    protected AlgoliaBusinessTester $tester;

    /**
     * @dataProvider getIndexableDataProvider
     *
     * @param array $expectedTypes
     */
    public function testFilterProductsConcreteFiltersIndexableProducts(bool $withPrices, int $expectedCount, array $expectedTypes): void
    {
        // Arrange
        $productsConcrete = $this->prepareTestProductsConcrete();
        $inactiveProductFilter = new ProductConcreteFilter();
        $algoliaConfigTransfer = $this->tester->haveAlgoliaConfigTransfer([AlgoliaConfigTransfer::IS_PRODUCT_PRICE_SYNCED => $withPrices]);

        // Act
        $filteredProductsConcrete = $inactiveProductFilter->filterIndexableProductsConcrete($productsConcrete, $algoliaConfigTransfer);

        // Assert
        $this->assertCount($expectedCount, $filteredProductsConcrete);

        $filteredProductsConcreteTypes = $this->extractFilteredProductsConcreteTypes($filteredProductsConcrete);

        $this->assertEquals($expectedTypes, $filteredProductsConcreteTypes);
    }

    /**
     * @return array<int, array>
     */
    public function getIndexableDataProvider(): array
    {
        return [
            [
                false,
                3,
                [
                    'ACTIVE_APPROVAL_NOT_DEFINED',
                    'ACTIVE_APPROVED',
                    'ACTIVE_APPROVED_ZERO_PRICE',
                ],
            ],
            [
                true,
                6,
                [
                    'ACTIVE_APPROVAL_NOT_DEFINED',
                    'ACTIVE_APPROVED',
                    'ACTIVE_APPROVED_WRONG_STORE',
                    'ACTIVE_APPROVED_NO_PRICE',
                    'ACTIVE_APPROVED_ZERO_PRICE',
                    'ACTIVE_APPROVED_NULL_PRICE',
                ],
            ],
        ];
    }

    /**
     * @dataProvider getNonIndexableDataProvider
     *
     * @param array $expectedTypes
     */
    public function testFilterProductsConcreteFiltersNonIndexableProducts(bool $withPrices, int $expectedCount, array $expectedTypes): void
    {
        // Arrange
        $productsConcrete = $this->prepareTestProductsConcrete();
        $inactiveProductFilter = new ProductConcreteFilter();
        $algoliaConfigTransfer = $this->tester->haveAlgoliaConfigTransfer([AlgoliaConfigTransfer::IS_PRODUCT_PRICE_SYNCED => $withPrices]);

        // Act
        $filteredProductsConcrete = $inactiveProductFilter->filterNonIndexableProductsConcrete($productsConcrete, $algoliaConfigTransfer);

        // Assert
        $this->assertCount($expectedCount, $filteredProductsConcrete);

        $filteredProductsConcreteTypes = $this->extractFilteredProductsConcreteTypes($filteredProductsConcrete);

        $this->assertEquals($expectedTypes, $filteredProductsConcreteTypes);
    }

    /**
     * @return array<int, array>
     */
    public function getNonIndexableDataProvider(): array
    {
        return [
            [
                false,
                8,
                [
                    'INACTIVE_APPROVAL_NOT_DEFINED',
                    'ACTIVE_APPROVED_WRONG_STORE',
                    'ACTIVE_UNAPPROVED',
                    'INACTIVE_APPROVED',
                    'INACTIVE_UNAPPROVED',
                    'ACTIVE_APPROVED_NO_PRICE',
                    'ACTIVE_APPROVED_NULL_PRICE',
                    'ACTIVE_APPROVED_EMPTY_STORE',
                ],
            ],
            [
                true,
                5,
                [
                    'INACTIVE_APPROVAL_NOT_DEFINED',
                    'ACTIVE_UNAPPROVED',
                    'INACTIVE_APPROVED',
                    'INACTIVE_UNAPPROVED',
                    'ACTIVE_APPROVED_EMPTY_STORE',
                ],
            ],
        ];
    }

    protected function prepareTestProductsConcrete(): ArrayObject
    {
        $productsConcrete = new ArrayObject();

        $storeBuilder = (new StoreBuilder([StoreTransfer::NAME => 'test-store']));

        $priceProductTransfer = (new PriceProductBuilder())
            ->withMoneyValue(
                (new MoneyValueBuilder([MoneyValueTransfer::GROSS_AMOUNT => 100]))
                    ->withStore(clone $storeBuilder)
                    ->withCurrency(),
            )->build();

        $zeroPriceProductTransfer = (new PriceProductBuilder())
            ->withMoneyValue(
                (new MoneyValueBuilder([MoneyValueTransfer::GROSS_AMOUNT => 0, MoneyValueTransfer::NET_AMOUNT => 0]))
                    ->withStore(clone $storeBuilder)
                    ->withCurrency(),
            )->build();

        $nullPriceProductTransfer = (new PriceProductBuilder())
            ->withMoneyValue(
                (new MoneyValueBuilder([MoneyValueTransfer::GROSS_AMOUNT => null, MoneyValueTransfer::NET_AMOUNT => null]))
                    ->withStore(clone $storeBuilder)
                    ->withCurrency(),
            )->build();

        $productsConcrete->append((new ProductConcreteTransfer())
            ->addStores(clone $storeBuilder->build())
            ->setAbstractSku('ACTIVE_APPROVAL_NOT_DEFINED')
            ->setIsActive(true)->addPrice($priceProductTransfer)->addProductAbstractPrice($priceProductTransfer));

        $productsConcrete->append((new ProductConcreteTransfer())
            ->addStores(clone $storeBuilder->build())
            ->setAbstractSku('INACTIVE_APPROVAL_NOT_DEFINED')
            ->setIsActive(false));

        $productsConcrete->append(
            (new ProductConcreteTransfer())
                ->addStores(clone $storeBuilder->build())
                ->setAbstractSku('ACTIVE_APPROVED')
                ->setIsActive(true)
            ->setApprovalStatus(ProductConcreteFilter::STATUS_APPROVED)
                ->addPrice($priceProductTransfer)->addProductAbstractPrice($priceProductTransfer),
        );

        $productsConcrete->append(
            (new ProductConcreteTransfer())
                ->addStores((new StoreBuilder())->build())
                ->setAbstractSku('ACTIVE_APPROVED_WRONG_STORE')
                ->setIsActive(true)
            ->setApprovalStatus(ProductConcreteFilter::STATUS_APPROVED)
                ->addPrice($priceProductTransfer)->addProductAbstractPrice($priceProductTransfer),
        );

        $productsConcrete->append(
            (new ProductConcreteTransfer())
                ->addStores(clone $storeBuilder->build())
                ->setAbstractSku('ACTIVE_UNAPPROVED')
                ->setIsActive(true)
            ->setApprovalStatus('STATUS_DIFFERENT_TO_APPROVED'),
        );

        $productsConcrete->append(
            (new ProductConcreteTransfer())
                ->addStores(clone $storeBuilder->build())
                ->setAbstractSku('INACTIVE_APPROVED')
                ->setIsActive(false)
            ->setApprovalStatus('ACTIVE_APPROVED'),
        );

        $productsConcrete->append(
            (new ProductConcreteTransfer())
                ->addStores(clone $storeBuilder->build())
                ->setAbstractSku('INACTIVE_UNAPPROVED')
                ->setIsActive(false)
            ->setApprovalStatus('STATUS_DIFFERENT_TO_APPROVED'),
        );

        $productsConcrete->append(
            (new ProductConcreteTransfer())
                ->addStores(clone $storeBuilder->build())
                ->setAbstractSku('ACTIVE_APPROVED_NO_PRICE')
                ->setIsActive(true)
                ->setApprovalStatus(ProductConcreteFilter::STATUS_APPROVED),
        );

        $productsConcrete->append(
            (new ProductConcreteTransfer())
                ->addStores(clone $storeBuilder->build())
                ->setAbstractSku('ACTIVE_APPROVED_ZERO_PRICE')
                ->setIsActive(true)
                ->setApprovalStatus(ProductConcreteFilter::STATUS_APPROVED)
                ->addPrice($priceProductTransfer)->addProductAbstractPrice($zeroPriceProductTransfer),
        );

        $productsConcrete->append(
            (new ProductConcreteTransfer())
                ->addStores(clone $storeBuilder->build())
                ->setAbstractSku('ACTIVE_APPROVED_NULL_PRICE')
                ->setIsActive(true)
                ->setApprovalStatus(ProductConcreteFilter::STATUS_APPROVED)
                ->addPrice($priceProductTransfer)->addProductAbstractPrice($nullPriceProductTransfer),
        );

        $productsConcrete->append(
            (new ProductConcreteTransfer())
                ->setAbstractSku('ACTIVE_APPROVED_EMPTY_STORE')
                ->setIsActive(true)
                ->setApprovalStatus(ProductConcreteFilter::STATUS_APPROVED)
                ->addPrice($priceProductTransfer)->addProductAbstractPrice($priceProductTransfer),
        );

        return $productsConcrete;
    }

    /**
     * @return array
     */
    protected function extractFilteredProductsConcreteTypes(ArrayObject $filteredProductsConcrete): array
    {
        $result = [];

        foreach ($filteredProductsConcrete as $filteredProductConcrete) {
            $result[] = $filteredProductConcrete->getAbstractSku();
        }

        return $result;
    }
}
