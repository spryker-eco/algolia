<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Zed\Algolia\Business\Mapper;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\AlgoliaConfigTransfer;
use Generated\Shared\Transfer\AlgoliaProductTransfer;
use Generated\Shared\Transfer\ProductConcreteTransfer;

/**
 * Auto-generated group annotations
 *
 * @group PyzTest
 * @group Zed
 * @group Algolia
 * @group Business
 * @group Mapper
 * @group ProductMapperTest
 * Add your own group annotations below this line
 */
class ProductMapperTest extends Unit
{
    /**
     * @var string
     */
    protected const STORE_REFERENCE_TEST = 'test-reference';

    /**
     * @var \SprykerEcoTest\Zed\Algolia\AlgoliaBusinessTester
     */
    protected $tester;

    /**
     * @return void
     */
    public function testMapProductConcreteToAlgoliaProductCollectionTransferWithEmptyDataReturnsZeroTransfers(): void
    {
        // Arrange
        $productConcreteTransfer = $this->tester->createEmptyProductConcreteTransfer('empty', 'empty-sku');

        $mapper = $this->tester->getFactory()->createProductMapper();

        // Act
        $algoliaProductsArray = $mapper->mapProductConcreteToAlgoliaProductTransfersArrayIndexedByStoreAndLocale($productConcreteTransfer, []);

        // Assert
        $this->assertCount(0, $algoliaProductsArray);
    }

    /**
     * @return void
     */
    public function testMapProductConcreteToAlgoliaProductCollectionTransferWithMinimalDataReturnsCorrectData(): void
    {
        // Arrange
        $productConcreteTransfer = $this->tester->createMinimalProductConcreteTransfer('minimal', 'minimal-sku');

        $storeName = $productConcreteTransfer->getStores()[0]->getName();
        $locale = $productConcreteTransfer->getLocalizedAttributes()[0]->getLocale()->getLocaleName();

        $mapper = $this->tester->getFactory()->createProductMapper();
        // Act
        $algoliaProductsArray = $mapper->mapProductConcreteToAlgoliaProductTransfersArrayIndexedByStoreAndLocale(
            $productConcreteTransfer,
            [],
        );

        // Assert
        // ProductConcreteTransfer provided contains 1 LocalizedAttribute which results to
        // mapping it to 1 AlgoliaProductTransfer
        $this->assertCount(1, $algoliaProductsArray);
        $this->assertCount(1, $algoliaProductsArray[$storeName]);

        $algoliaProductTransfer = $algoliaProductsArray[$storeName][$locale][0];
        $this->assertEquals('minimal-sku', $algoliaProductTransfer->getObject()->getSku());
        // Check if AlgoliaProductTransfer locale is correctly mapped based on the
        // locale of LocalizedAttributeTransfer in ProductConcreteTransfer
        $this->assertEquals($locale, $algoliaProductTransfer->getMetadata()->getLocale());
    }

    /**
     * @return void
     */
    public function testMapProductConcreteToAlgoliaProductCollectionTransferWithFullDataReturnsCorrectData(): void
    {
        // Arrange
        $productConcreteTransfer = $this->tester->createFullProductConcreteTransfer([ProductConcreteTransfer::NAME => 'full', ProductConcreteTransfer::SKU => 'full-sku']);

        $storeName = $productConcreteTransfer->getStores()[0]->getName();
        $locale = $productConcreteTransfer->getLocalizedAttributes()[0]->getLocale()->getLocaleName();
        $anotherLocale = $productConcreteTransfer->getLocalizedAttributes()[1]->getLocale()->getLocaleName();
        $localizedAttributesKeys = array_keys($productConcreteTransfer->getLocalizedAttributes()[0]->getAttributes());
        $categoryName = $productConcreteTransfer->getRelatedCategoryTreeNodes()[0]->getChildrenNodes()->getNodes()[0]->getCategory()->getLocalizedAttributes()[0]->getName();
        $differentLocaleCategoryName = $productConcreteTransfer->getRelatedCategoryTreeNodes()[0]->getChildrenNodes()->getNodes()[0]->getCategory()->getLocalizedAttributes()[1]->getName();
        $price = $productConcreteTransfer->getPrices()[0]->getMoneyValue()->getGrossAmount();
        $productAbstractPrice = $productConcreteTransfer->getProductAbstractPrices()[0]->getMoneyValue()->getGrossAmount();
        $firstLevelCategoryName = $productConcreteTransfer->getRelatedCategoryTreeNodes()[0]->getCategory()->getLocalizedAttributes()[0]->getName();
        $secondLevelCategoryName = $firstLevelCategoryName . ' > ' . $productConcreteTransfer->getRelatedCategoryTreeNodes()[0]->getChildrenNodes()->getNodes()[0]->getCategory()->getLocalizedAttributes()[0]->getName();

        $mapper = $this->tester->getFactory()->createProductMapper();
        // Act
        $algoliaProductsArray = $mapper->mapProductConcreteToAlgoliaProductTransfersArrayIndexedByStoreAndLocale(
            $productConcreteTransfer,
            [],
        );

        // Assert
        // ProductConcreteTransfer provided contains 2 LocalizedAttributeTransfers with different locales
        // which results to mapping it to 2 AlgoliaProductTransfers
        $this->assertCount(1, $algoliaProductsArray);
        $this->assertCount(2, $algoliaProductsArray[$storeName]);

        $algoliaProductTransfer = $algoliaProductsArray[$storeName][$locale][0];
        $this->assertEquals($locale, $algoliaProductTransfer->getMetadata()->getLocale());

        // Check if AlgoliaProductTransfer.localizedAttributes are correctly mapped to AlgoliaProductTransfer
        $this->assertArrayHasKey($localizedAttributesKeys[0], $algoliaProductTransfer->getObject()->getAttributes());
        // Check if AlgoliaProductTransfer.categories are correctly mapped to AlgoliaProductTransfer
        $this->assertContains($categoryName, $algoliaProductTransfer->getObject()->getCategory());
        // Check if AlgoliaProductTransfer.price are correctly
        // mapped to AlgoliaProductTransfer (least gross price is selected)
        $this->assertEquals($productAbstractPrice, $algoliaProductTransfer->getObject()->getPrices()['eur']->getGross());
        $this->assertEquals($price, $algoliaProductTransfer->getObject()->getConcretePrices()['eur']->getGross());

        $differentLocaleAlgoliaProductTransfer = $algoliaProductsArray[$storeName][$anotherLocale][0];
        $this->assertContains($differentLocaleCategoryName, $differentLocaleAlgoliaProductTransfer->getObject()->getCategory());
        // Check if AlgoliaProductTransfer.categories are correctly mapped to AlgoliaProductTransfer and
        // there is no mix between categories with different locales
        $this->assertNotEquals($algoliaProductTransfer->getObject()->getCategory(), $differentLocaleAlgoliaProductTransfer->getObject()->getCategory());

        // Check if `hierarchicalCategories` field was formatted correctly based on relatedCategoryTreeNodes field of productConcrete
        $this->assertEquals($firstLevelCategoryName, $algoliaProductTransfer->getObject()->getHierarchicalCategories()['lvl0']);
        $this->assertContains($secondLevelCategoryName, $algoliaProductTransfer->getObject()->getHierarchicalCategories()['lvl1']);

        // Check if `searchMetadata` field was mapped
        $this->assertEquals($productConcreteTransfer->getSearchMetadata(), $algoliaProductTransfer->getObject()->getSearchMetadata());
    }

    /**
     * @return void
     */
    public function testMapAlgoliaProductTransfersArrayToAlgoliaObjectArrayCorrectlyMapsObjectIdAndOtherData(): void
    {
        // Arrange
        $algoliaObjectTransfer = $this->tester->createAlgoliaObjectTransfer();
        $algoliaProductTransfer = (new AlgoliaProductTransfer())
            ->setObject($algoliaObjectTransfer);
        $algoliaConfigTransfer = $this->tester->haveAlgoliaConfigTransfer([AlgoliaConfigTransfer::IS_PRODUCT_PRICE_SYNCED => true]);
        $mapper = $this->tester->getFactory()->createProductMapper();

        // Act
        $algoliaObjectArrays = $mapper->mapAlgoliaProductTransfersArrayToAlgoliaObjectArray([$algoliaProductTransfer], $algoliaConfigTransfer);

        // Assert
        $this->assertCount(1, $algoliaObjectArrays);
        $algoliaObjectArray = $algoliaObjectArrays[0];

        $this->assertArrayHasKey('objectID', $algoliaObjectArray);
        $this->assertArrayNotHasKey('object_id', $algoliaObjectArray);
        $this->assertNotEmpty($algoliaObjectArray['sku']);
    }

    /**
     * @return void
     */
    public function testMapAlgoliaProductTransfersArrayToAlgoliaObjectArrayWithoutPrice(): void
    {
        // Arrange
        $algoliaObjectTransfer = $this->tester->createAlgoliaObjectTransfer();
        $algoliaProductTransfer = (new AlgoliaProductTransfer())
            ->setObject($algoliaObjectTransfer);

        $mapper = $this->tester->getFactory()->createProductMapper();
        $algoliaConfigTransfer = $this->tester->haveAlgoliaConfigTransfer([AlgoliaConfigTransfer::IS_PRODUCT_PRICE_SYNCED => false]);

        // Act
        $algoliaObjectArrays = $mapper->mapAlgoliaProductTransfersArrayToAlgoliaObjectArray([$algoliaProductTransfer], $algoliaConfigTransfer);

        // Assert
        $this->assertCount(1, $algoliaObjectArrays);
        $algoliaObjectArray = $algoliaObjectArrays[0];

        $this->assertArrayNotHasKey('prices', $algoliaObjectArray);
        $this->assertArrayNotHasKey('concrete_prices', $algoliaObjectArray);
    }

    /**
     * @return void
     */
    public function testMapProductConcreteToAlgoliaProductCollectionTransferWithNotSearchableParentCategoryFullDataReturnsCorrectData(): void
    {
        // Arrange
        $productConcreteTransfer = $this->tester->createFullProductConcreteTransfer([
            ProductConcreteTransfer::NAME => 'full',
            ProductConcreteTransfer::SKU => 'full-sku',
        ]);

        $productConcreteTransfer->getRelatedCategoryTreeNodes()[0]->getCategory()->setIsSearchable(false);
        $locale = $productConcreteTransfer->getLocalizedAttributes()[0]->getLocale()->getLocaleName();
        $mapper = $this->tester->getFactory()->createProductMapper();

        // Act
        $algoliaProductsArray = $mapper->mapProductConcreteToAlgoliaProductTransfersArrayIndexedByStoreAndLocale(
            $productConcreteTransfer,
            [],
        );

        // Assert
        $algoliaProductTransfer = $algoliaProductsArray[$productConcreteTransfer->getName()][$locale][0];
        $this->assertCount(2, $algoliaProductTransfer->getObject()->getCategory());
    }

    /**
     * @return void
     */
    public function testMapProductConcreteToAlgoliaProductCollectionTransferWithNotSearchableChildsCategoryFullDataReturnsCorrectData(): void
    {
        // Arrange
        $productConcreteTransfer = $this->tester->createFullProductConcreteTransfer([
            ProductConcreteTransfer::NAME => 'full',
            ProductConcreteTransfer::SKU => 'full-sku',
        ]);

        $productConcreteTransfer->getRelatedCategoryTreeNodes()[0]->getChildrenNodes()->getNodes()[0]->getCategory()->setIsSearchable(false);
        $productConcreteTransfer->getRelatedCategoryTreeNodes()[0]->getChildrenNodes()->getNodes()[1]->getCategory()->setIsSearchable(false);
        $locale = $productConcreteTransfer->getLocalizedAttributes()[0]->getLocale()->getLocaleName();
        $mapper = $this->tester->getFactory()->createProductMapper();

        // Act
        $algoliaProductsArray = $mapper->mapProductConcreteToAlgoliaProductTransfersArrayIndexedByStoreAndLocale(
            $productConcreteTransfer,
            [],
        );

        // Assert
        $algoliaProductTransfer = $algoliaProductsArray[$productConcreteTransfer->getName()][$locale][0];
        $this->assertCount(1, $algoliaProductTransfer->getObject()->getCategory());
    }

    /**
     * @return void
     */
    public function testMapProductConcreteToAlgoliaProductCollectionTransferWithNotSearchableChildsCategoryWithPricingIdFullDataReturnsCorrectData(): void
    {
        // Arrange
        $productConcreteTransfer = $this->tester->createFullProductConcreteTransfer([
            ProductConcreteTransfer::NAME => 'full',
            ProductConcreteTransfer::SKU => 'full-sku',
        ]);

        $productConcreteTransfer->getPrices()[0]->setIdPriceProduct(1);
        $productConcreteTransfer->getProductAbstractPrices()[0]->setIdPriceProduct(1);

        $locale = $productConcreteTransfer->getLocalizedAttributes()[0]->getLocale()->getLocaleName();
        $mapper = $this->tester->getFactory()->createProductMapper();

        // Act
        $algoliaProductsArray = $mapper->mapProductConcreteToAlgoliaProductTransfersArrayIndexedByStoreAndLocale(
            $productConcreteTransfer,
            [],
        );

        // Assert
        $algoliaProductTransfer = $algoliaProductsArray[$productConcreteTransfer->getName()][$locale][0];
        $this->assertCount(3, $algoliaProductTransfer->getObject()->getCategory());
    }
}
