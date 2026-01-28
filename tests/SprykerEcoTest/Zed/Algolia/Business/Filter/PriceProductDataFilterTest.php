<?php

/**
 * Copyright © 2022-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Zed\Algolia\Business\Filter;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\MoneyValueTransfer;
use Generated\Shared\Transfer\PriceProductDimensionTransfer;
use Generated\Shared\Transfer\PriceProductTransfer;
use Generated\Shared\Transfer\PriceTypeTransfer;
use Generated\Shared\Transfer\ProductConcreteTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use SprykerEco\Zed\Algolia\Business\Filter\PriceProductDataFilter;

/**
 * Auto-generated group annotations
 *
 * @group PyzTest
 * @group Zed
 * @group Algolia
 * @group Business
 * @group Filter
 * @group PriceProductDataFilterTest
 * Add your own group annotations below this line
 */
class PriceProductDataFilterTest extends Unit
{
    /**
     * @var string
     */
    protected const STORE_NAME_CORRECT = 'CORRECT';

    /**
     * @var string
     */
    protected const STORE_NAME_INCORRECT = 'INCORRECT';

    /**
     * @dataProvider priceDataProvider
     *
     * @param \Generated\Shared\Transfer\StoreTransfer $storeTransfer
     * @param \Generated\Shared\Transfer\PriceProductTransfer $priceProductTransfer
     * @param int $expectedPricesCount
     *
     * @return void
     */
    public function testFilterProductDataFiltersOutIncorrectPrices(
        StoreTransfer $storeTransfer,
        PriceProductTransfer $priceProductTransfer,
        int $expectedPricesCount
    ): void {
        $productConcrete = $this->createProductConcrete($storeTransfer, $priceProductTransfer);

        $result = (new PriceProductDataFilter())
            ->filterProductData($productConcrete);

        $this->assertCount($expectedPricesCount, $result->getPrices());
        $this->assertCount($expectedPricesCount, $result->getProductAbstractPrices());
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected function priceDataProvider(): array
    {
        $correctStore = (new StoreTransfer())
            ->setName(static::STORE_NAME_CORRECT);

        $incorrectStore = (new StoreTransfer())
            ->setName(static::STORE_NAME_INCORRECT);

        return [
            'valid price' => [
                'storeTransfer' => $correctStore,
                'priceProductTransfer' => (new PriceProductTransfer())
                    ->setMoneyValue((new MoneyValueTransfer())->setStore($correctStore))
                    ->setPriceDimension((new PriceProductDimensionTransfer())->setName('Default'))
                    ->setPriceType((new PriceTypeTransfer())->setName('DEFAULT')),
                'expectedPricesCount' => 1,
            ],
            'invalid store' => [
                'storeTransfer' => $correctStore,
                'priceProductTransfer' => (new PriceProductTransfer())
                            ->setMoneyValue((new MoneyValueTransfer())->setStore($incorrectStore)),
                'expectedPricesCount' => 0,
            ],
            'invalid price product dimension' => [
                'storeTransfer' => $correctStore,
                'priceProductTransfer' => (new PriceProductTransfer())
                    ->setMoneyValue((new MoneyValueTransfer())->setStore($correctStore))
                    ->setPriceDimension((new PriceProductDimensionTransfer())->setName('INVALID_PRICE_DIMENSION')),
                'expectedPricesCount' => 0,
            ],
            'invalid price type' => [
                'storeTransfer' => $correctStore,
                'priceProductTransfer' => (new PriceProductTransfer())
                    ->setMoneyValue((new MoneyValueTransfer())->setStore($correctStore))
                    ->setPriceDimension((new PriceProductDimensionTransfer())->setName('INVALID_PRICE_DIMENSION'))
                    ->setPriceType((new PriceTypeTransfer())->setName('INVALID_PRICE_TYPE')),
                'expectedPricesCount' => 0,
            ],
            'invalid volume quantity' => [
                'storeTransfer' => $correctStore,
                'priceProductTransfer' => (new PriceProductTransfer())
                    ->setMoneyValue((new MoneyValueTransfer())->setStore($correctStore))
                    ->setPriceDimension((new PriceProductDimensionTransfer())->setName('INVALID_PRICE_DIMENSION'))
                    ->setPriceType((new PriceTypeTransfer())->setName('INVALID_PRICE_TYPE'))
                    ->setVolumeQuantity(100),
                'expectedPricesCount' => 0,
            ],
        ];
    }

    /**
     * @param \Generated\Shared\Transfer\StoreTransfer $storeTransfer
     * @param \Generated\Shared\Transfer\PriceProductTransfer $priceProductTransfer
     *
     * @return \Generated\Shared\Transfer\ProductConcreteTransfer
     */
    protected function createProductConcrete(
        StoreTransfer $storeTransfer,
        PriceProductTransfer $priceProductTransfer
    ): ProductConcreteTransfer {
        return (new ProductConcreteTransfer())
            ->setAbstractSku('FILTERABLE_PRODUCT_CONCRETE')
            ->addPrice($priceProductTransfer)
            ->addStores($storeTransfer)
            ->addProductAbstractPrice($priceProductTransfer);
    }
}
