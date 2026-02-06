<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Zed\Algolia\Business\Indexer;

use ArrayObject;
use Codeception\Test\Unit;
use Generated\Shared\Transfer\IndexedAlgoliaProductCollectionTransfer;
use Generated\Shared\Transfer\LocaleTransfer;
use Generated\Shared\Transfer\LocalizedAttributesTransfer;
use Generated\Shared\Transfer\ProductConcreteTransfer;

/**
 * Auto-generated group annotations
 *
 * @group SprykerEcoTest
 * @group Zed
 * @group Algolia
 * @group Business
 * @group Indexer
 * @group ProductIndexerTest
 * Add your own group annotations below this line
 */
class ProductIndexerTest extends Unit
{
    /**
     * @var string
     */
    protected const TEST_TENANT_ID = 'test';

    /**
     * @var string
     */
    protected const TEST_WRONG_TENANT_ID = 'test-wrong';

    /**
     * @var \SprykerEcoTest\Zed\Algolia\AlgoliaBusinessTester
     */
    protected $tester;

    /**
     * @return void
     */
    public function testIndexProductsConcreteByStoreAndLocaleIndexesProvidedArray(): void
    {
        // Arrange
        $productConcreteTransfer = $this->tester->haveFullProductConcreteTransfer(
            [
                ProductConcreteTransfer::NAME => 'full',
                ProductConcreteTransfer::SKU => 'full-sku',
                ProductConcreteTransfer::LOCALIZED_ATTRIBUTES => [
                    [LocalizedAttributesTransfer::LOCALE => [LocaleTransfer::LOCALE_NAME => 'de_DE']],
                    [LocalizedAttributesTransfer::LOCALE => [LocaleTransfer::LOCALE_NAME => 'en_US']],
                ],
            ],
        );
        $anotherProductConcreteTransfer = $this->tester->haveFullProductConcreteTransfer(
            [
                ProductConcreteTransfer::NAME => 'full-another',
                ProductConcreteTransfer::SKU => 'full-sku-another',
                ProductConcreteTransfer::LOCALIZED_ATTRIBUTES => [
                    [LocalizedAttributesTransfer::LOCALE => [LocaleTransfer::LOCALE_NAME => 'de_CH']],
                    [LocalizedAttributesTransfer::LOCALE => [LocaleTransfer::LOCALE_NAME => 'en_GB']],
                ],
            ],
        );

        $locale = $productConcreteTransfer->getLocalizedAttributes()[0]->getLocale()->getLocaleName();
        $anotherLocale = $productConcreteTransfer->getLocalizedAttributes()[1]->getLocale()->getLocaleName();

        $anotherProductLocale = $anotherProductConcreteTransfer->getLocalizedAttributes()[0]->getLocale()->getLocaleName();
        $anotherProductAnotherLocale = $anotherProductConcreteTransfer->getLocalizedAttributes()[1]->getLocale()->getLocaleName();

        $indexer = $this->tester->getFactory()->createProductIndexer();
        // Act
        $indexedAlgoliaProductCollections = $indexer->indexProductsConcreteByStoreAndLocale(
            new ArrayObject(
                [
                    $productConcreteTransfer,
                    $anotherProductConcreteTransfer,
                ],
            ),
            static::TEST_TENANT_ID,
        );

        // Assert
        $this->assertCount(4, $indexedAlgoliaProductCollections);

        $indices = array_map(function (IndexedAlgoliaProductCollectionTransfer $indexedAlgoliaProductCollectionTransfer) {
            return $indexedAlgoliaProductCollectionTransfer->getIndexName();
        }, $indexedAlgoliaProductCollections);

        $indicesString = implode(',', $indices);

        $this->assertStringContainsString($this->getLanguageNameFromLocale($locale), $indicesString);
        $this->assertStringContainsString($this->getLanguageNameFromLocale($anotherLocale), $indicesString);
        $this->assertStringContainsString($this->getLanguageNameFromLocale($anotherProductLocale), $indicesString);
        $this->assertStringContainsString($this->getLanguageNameFromLocale($anotherProductAnotherLocale), $indicesString);
        $this->assertStringContainsString(static::TEST_TENANT_ID, $indicesString);
    }

    /**
     * @return void
     */
    public function testIndexProductsConcreteByStoreAndLocaleIndexesProvidedArrayWithMatchingLocales(): void
    {
        // Arrange
        $productConcreteTransfer = $this->tester->haveFullProductConcreteTransfer(
            [
                ProductConcreteTransfer::NAME => 'full',
                ProductConcreteTransfer::SKU => 'full-sku',
            ],
        );
        $anotherProductConcreteTransfer = $this->tester->haveFullProductConcreteTransfer(
            [
                ProductConcreteTransfer::NAME => 'full-another',
                ProductConcreteTransfer::SKU => 'full-sku-another',
            ],
        );

        $locale = $productConcreteTransfer->getLocalizedAttributes()[0]->getLocale()->getLocaleName();
        $anotherLocale = $productConcreteTransfer->getLocalizedAttributes()[1]->getLocale()->getLocaleName();

        $anotherProductLocale = $anotherProductConcreteTransfer->getLocalizedAttributes()[0]->getLocale()->getLocaleName();
        $anotherProductConcreteTransfer->getLocalizedAttributes()[1]->setLocale($productConcreteTransfer->getLocalizedAttributes()[1]->getLocale());
        $anotherProductAnotherLocale = $anotherProductConcreteTransfer->getLocalizedAttributes()[1]->getLocale()->getLocaleName();
        $anotherProductConcreteTransfer->setRelatedCategoryTreeNodes(new ArrayObject());

        $indexer = $this->tester->getFactory()->createProductIndexer();
        // Act
        $indexedAlgoliaProductCollections = $indexer->indexProductsConcreteByStoreAndLocale(
            new ArrayObject(
                [
                    $productConcreteTransfer,
                    $anotherProductConcreteTransfer,
                ],
            ),
            static::TEST_TENANT_ID,
        );

        // Assert
        $this->assertCount(4, $indexedAlgoliaProductCollections);

        $indices = array_map(function (IndexedAlgoliaProductCollectionTransfer $indexedAlgoliaProductCollectionTransfer) {
            return $indexedAlgoliaProductCollectionTransfer->getIndexName();
        }, $indexedAlgoliaProductCollections);

        $indicesString = implode(',', $indices);

        $this->assertStringContainsString($this->getLanguageNameFromLocale($locale), $indicesString);
        $this->assertStringContainsString($this->getLanguageNameFromLocale($anotherLocale), $indicesString);
        $this->assertStringContainsString($this->getLanguageNameFromLocale($anotherProductLocale), $indicesString);
        $this->assertStringContainsString($this->getLanguageNameFromLocale($anotherProductAnotherLocale), $indicesString);
        $this->assertStringContainsString(static::TEST_TENANT_ID, $indicesString);
    }

    /**
     * @return void
     */
    public function testIndexProductsConcreteByStoreAndLocaleIndexesProvidedArrayWithoutIndexingProductWithUnmatchingStore(): void
    {
        // Arrange
        $productConcreteTransfer = $this->tester->haveFullProductConcreteTransfer(
            [
                ProductConcreteTransfer::NAME => 'full',
                ProductConcreteTransfer::SKU => 'full-sku',
            ],
        );
        $anotherProductConcreteTransfer = $this->tester->haveFullProductConcreteTransfer(
            [
                ProductConcreteTransfer::NAME => 'full-another',
                ProductConcreteTransfer::SKU => 'full-sku-another',
            ],
        );

        $indexer = $this->tester->getFactory()->createProductIndexer();

        // Act
        $indexedAlgoliaProductCollections = $indexer->indexProductsConcreteByStoreAndLocale(
            new ArrayObject(
                [
                    $productConcreteTransfer,
                    $anotherProductConcreteTransfer,
                ],
            ),
            static::TEST_TENANT_ID,
        );

        // Assert
        $this->assertCount(4, $indexedAlgoliaProductCollections);

        $indices = array_map(function (IndexedAlgoliaProductCollectionTransfer $indexedAlgoliaProductCollectionTransfer) {
            return $indexedAlgoliaProductCollectionTransfer->getIndexName();
        }, $indexedAlgoliaProductCollections);

        $indicesString = implode(',', $indices);

        $this->assertStringContainsString(static::TEST_TENANT_ID, $indicesString);
        $this->assertStringNotContainsString(static::TEST_WRONG_TENANT_ID, $indicesString);
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    protected function getLanguageNameFromLocale(string $locale): string
    {
        return explode('_', $locale)[0];
    }
}
