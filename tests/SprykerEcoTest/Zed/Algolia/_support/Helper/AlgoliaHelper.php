<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Zed\Algolia\Helper;

use Algolia\AlgoliaSearch\Exceptions\BadRequestException;
use Codeception\Module;
use Generated\Shared\DataBuilder\AlgoliaApiCredentialsBuilder;
use Generated\Shared\DataBuilder\AlgoliaProductObjectBuilder;
use Generated\Shared\DataBuilder\CategoryBuilder;
use Generated\Shared\DataBuilder\CategoryLocalizedAttributesBuilder;
use Generated\Shared\DataBuilder\LocaleBuilder;
use Generated\Shared\DataBuilder\LocalizedAttributesBuilder;
use Generated\Shared\DataBuilder\LocalizedUrlBuilder;
use Generated\Shared\DataBuilder\MoneyValueBuilder;
use Generated\Shared\DataBuilder\NodeBuilder;
use Generated\Shared\DataBuilder\NodeCollectionBuilder;
use Generated\Shared\DataBuilder\PriceProductBuilder;
use Generated\Shared\DataBuilder\ProductConcreteBuilder;
use Generated\Shared\DataBuilder\ProductCreatedBuilder;
use Generated\Shared\DataBuilder\ProductDeletedBuilder;
use Generated\Shared\DataBuilder\ProductExportedBuilder;
use Generated\Shared\DataBuilder\ProductImageSetBuilder;
use Generated\Shared\DataBuilder\ProductLabelBuilder;
use Generated\Shared\DataBuilder\ProductLabelLocalizedAttributesBuilder;
use Generated\Shared\DataBuilder\ProductOfferBuilder;
use Generated\Shared\DataBuilder\ProductUpdatedBuilder;
use Generated\Shared\DataBuilder\StoreBuilder;
use Generated\Shared\Transfer\AlgoliaApiCredentialsTransfer;
use Generated\Shared\Transfer\AlgoliaProductObjectTransfer;
use Generated\Shared\Transfer\LocalizedAttributesTransfer;
use Generated\Shared\Transfer\MessageAttributesTransfer;
use Generated\Shared\Transfer\MoneyValueTransfer;
use Generated\Shared\Transfer\PriceProductDimensionTransfer;
use Generated\Shared\Transfer\PriceTypeTransfer;
use Generated\Shared\Transfer\ProductConcreteTransfer;
use Generated\Shared\Transfer\ProductCreatedTransfer;
use Generated\Shared\Transfer\ProductDeletedTransfer;
use Generated\Shared\Transfer\ProductExportedTransfer;
use Generated\Shared\Transfer\ProductUpdatedTransfer;
use Generated\Shared\Transfer\ProductUrlTransfer;
use Generated\Shared\Transfer\StoreTransfer;

class AlgoliaHelper extends Module
{
    /**
     * @param array $seed
     */
    public function haveFullProductConcreteTransfer(
        array $seed = []
    ): ProductConcreteTransfer {
        // Create locale seed data once, explicitly setting locale_name to ensure consistency
        $locale1Seed = $seed[ProductConcreteTransfer::LOCALIZED_ATTRIBUTES][0][LocalizedAttributesTransfer::LOCALE] ?? [];
        $locale2Seed = $seed[ProductConcreteTransfer::LOCALIZED_ATTRIBUTES][1][LocalizedAttributesTransfer::LOCALE] ?? [];

        // Build locales once to get names, then add names back to seed to prevent Faker randomization
        $tempLocale1 = (new LocaleBuilder($locale1Seed))->build();
        $tempLocale2 = (new LocaleBuilder($locale2Seed))->build();

        $locale1Seed['locale_name'] = $tempLocale1->getLocaleName();
        $locale2Seed['locale_name'] = $tempLocale2->getLocaleName();

        $availableLocaleIsoCodes = array_unique([
            $locale1Seed['locale_name'],
            $locale2Seed['locale_name'],
        ]);

        $storeBuilder = (new StoreBuilder(
            $seed + [
                StoreTransfer::AVAILABLE_LOCALE_ISO_CODES => $availableLocaleIsoCodes,
            ],
        ));

        $category1Builder = (new CategoryBuilder())
            ->withAnotherLocalizedAttributes(
                (new CategoryLocalizedAttributesBuilder())
                    ->withLocale($locale1Seed),
            )
            ->withAnotherLocalizedAttributes(
                (new CategoryLocalizedAttributesBuilder())
                    ->withLocale($locale2Seed),
            );

        $category2Builder = (new CategoryBuilder())
            ->withAnotherLocalizedAttributes(
                (new CategoryLocalizedAttributesBuilder())
                    ->withLocale($locale1Seed),
            )
            ->withAnotherLocalizedAttributes(
                (new CategoryLocalizedAttributesBuilder())
                    ->withLocale($locale2Seed),
            );

        $priceProductTransfer = (new PriceProductBuilder())
            ->withMoneyValue(
                (new MoneyValueBuilder([MoneyValueTransfer::GROSS_AMOUNT => 90]))
                    ->withStore($storeBuilder)
                    ->withCurrency(),
            )->withPriceDimension([
                PriceProductDimensionTransfer::NAME => 'Default',
            ])
            ->withPriceType([
                PriceTypeTransfer::NAME => 'DEFAULT',
            ])->build();

        $productConcreteTransfer = (new ProductConcreteBuilder($seed))
            ->withImageSet(
                (new ProductImageSetBuilder())
                    ->withProductImage(),
                // without locale (default locale will be used)
            )
            ->withAnotherImageSet(
                (new ProductImageSetBuilder())
                    ->withProductImage()
                    ->withLocale($locale2Seed),
            )
            ->withStores($storeBuilder)
            ->withAnotherProductLabel(
                (new ProductLabelBuilder())->withAnotherLocalizedAttributes(
                    (new ProductLabelLocalizedAttributesBuilder())->withAnotherLocale($locale1Seed),
                ),
            )
            ->withAnotherStores(
                $seed + [
                    StoreTransfer::AVAILABLE_LOCALE_ISO_CODES => $availableLocaleIsoCodes,
                ],
            )
            ->withAnotherPrice(
                (new PriceProductBuilder())
                    ->withMoneyValue(
                        (new MoneyValueBuilder([MoneyValueTransfer::GROSS_AMOUNT => 90]))
                            ->withStore($storeBuilder)
                            ->withCurrency(),
                    )
                    ->withPriceDimension([
                        PriceProductDimensionTransfer::NAME => 'Default',
                    ])
                    ->withPriceType([
                        PriceTypeTransfer::NAME => 'DEFAULT',
                    ]),
            )
            ->withAnotherPrice(
                (new PriceProductBuilder())
                    ->withMoneyValue(
                        (new MoneyValueBuilder([MoneyValueTransfer::GROSS_AMOUNT => 190]))
                            ->withStore($storeBuilder)
                            ->withCurrency(),
                    )
                    ->withPriceDimension([
                        PriceProductDimensionTransfer::NAME => 'Default',
                    ])
                    ->withPriceType([
                        PriceTypeTransfer::NAME => 'DEFAULT',
                    ]),
            )
            ->withAnotherProductAbstractPrice(
                (new PriceProductBuilder())
                    ->withMoneyValue(
                        (new MoneyValueBuilder([MoneyValueTransfer::GROSS_AMOUNT => 100]))
                            ->withStore($storeBuilder)
                            ->withCurrency(),
                    )
                    ->withPriceDimension([
                        PriceProductDimensionTransfer::NAME => 'Default',
                    ])
                    ->withPriceType([
                        PriceTypeTransfer::NAME => 'DEFAULT',
                    ]),
            )
            ->withAnotherProductAbstractPrice(
                (new PriceProductBuilder())
                    ->withMoneyValue(
                        (new MoneyValueBuilder([MoneyValueTransfer::GROSS_AMOUNT => 200]))
                            ->withStore($storeBuilder)
                            ->withCurrency(),
                    )
                    ->withPriceDimension([
                        PriceProductDimensionTransfer::NAME => 'Default',
                    ])
                    ->withPriceType([
                        PriceTypeTransfer::NAME => 'DEFAULT',
                    ]),
            )
            ->withOffer((new ProductOfferBuilder())->withStore($seed))
            ->withAnotherOffer()
            ->build();

        return $productConcreteTransfer
            ->addPrice($priceProductTransfer)
            ->setIsActive(true)
            ->setAttributes(['attribute1' => 'attributeValue1'])
            ->addLocalizedAttributes(
                (new LocalizedAttributesBuilder())
                    ->withLocale($locale1Seed)
                    ->build()
                    ->setAttributes([
                        'localizedAttribute1' => 'localizedAttribute1Value1',
                    ]),
            )
            ->addLocalizedAttributes(
                (new LocalizedAttributesBuilder())
                    ->withLocale($locale2Seed)
                    ->build()
                    ->setAttributes([
                        'localizedAttribute1' => 'localizedAttribute1Value2',
                    ]),
            )
            ->setUrl((new ProductUrlTransfer())
                ->addUrl((new LocalizedUrlBuilder())->withLocale($locale1Seed)->build())
                ->addUrl((new LocalizedUrlBuilder())->withLocale($locale2Seed)->build()))
            ->addRelatedCategoryTreeNode(
                (new NodeBuilder())->withCategory(
                    (new CategoryBuilder())
                        ->withAnotherLocalizedAttributes(
                            (new CategoryLocalizedAttributesBuilder())
                                ->withLocale($locale1Seed),
                        )
                        ->withAnotherLocalizedAttributes(
                            (new CategoryLocalizedAttributesBuilder())
                                ->withLocale($locale2Seed),
                        ),
                )->build()
                    ->setChildrenNodes(
                        (new NodeCollectionBuilder())
                            ->withNode(
                                (new NodeBuilder())->withCategory($category1Builder),
                            )
                            ->withAnotherNode(
                                (new NodeBuilder())->withCategory($category2Builder),
                            )
                        ->build(),
                    ),
            );
    }

    public function haveEmptyProductConcreteTransfer(string $name, string $sku): ProductConcreteTransfer
    {
        $storeEn = (new StoreBuilder())->build();

        return (new ProductConcreteTransfer())
            ->setName($name)
            ->setSku($sku)
            ->setAbstractSku('abstract_' . $sku)
            ->setIsActive(true)
            ->addStores($storeEn);
    }

    public function haveMinimalProductConcreteTransfer(string $name, string $sku): ProductConcreteTransfer
    {
        $localeEn = (new LocaleBuilder())->build()
            ->setIsActive(true);

        $storeEn = (new StoreBuilder([
            StoreTransfer::AVAILABLE_LOCALE_ISO_CODES => [
                $localeEn->getLocaleName(),
            ],
        ]))->build();

        $localizedAttributeEn = (new LocalizedAttributesBuilder())->build()
            ->setLocale($localeEn)
            ->setAttributes([
                'localizedAttribute1' => 'localizedAttribute1ValueEn',
            ]);

        return (new ProductConcreteTransfer())
            ->setName($name)
            ->setSku($sku)
            ->setAbstractSku('abstract_' . $sku)
            ->setIsActive(true)
            ->setAttributes(['attribute1' => 'attributeValue1'])
            ->addLocalizedAttributes($localizedAttributeEn)
            ->addStores($storeEn);
    }

    /**
     * @param array $seed
     */
    public function haveAlgoliaObjectTransfer(array $seed = []): AlgoliaProductObjectTransfer
    {
        return (new AlgoliaProductObjectBuilder($seed))->build();
    }

    public function haveProductExportedTransferFromConcrete(ProductConcreteTransfer $productConcreteTransfer): ProductExportedTransfer
    {
        $messageAttributesTransfer = (new MessageAttributesTransfer())
            ->setStoreReference('storeReference');

        return (new ProductExportedTransfer())
            ->addProductConcrete($productConcreteTransfer)
            ->setMessageAttributes($messageAttributesTransfer);
    }

    /**
     * @param array $seed
     */
    public function haveProductExportedTransfer(array $seed = []): ProductExportedTransfer
    {
        return (new ProductExportedBuilder($seed))->build();
    }

    /**
     * @param array $seed
     */
    public function haveProductCreatedTransfer(array $seed = []): ProductCreatedTransfer
    {
        return (new ProductCreatedBuilder($seed))->build();
    }

    /**
     * @param array $seed
     */
    public function haveProductUpdatedTransfer(array $seed = []): ProductUpdatedTransfer
    {
        return (new ProductUpdatedBuilder($seed))->build();
    }

    /**
     * @param array $seed
     */
    public function haveProductDeletedTransfer(array $seed = []): ProductDeletedTransfer
    {
        return (new ProductDeletedBuilder($seed))->build();
    }

    /**
     * @param array $seed
     */
    public function haveAlgoliaApiCredentialsTransfer(array $seed = []): AlgoliaApiCredentialsTransfer
    {
        return (new AlgoliaApiCredentialsBuilder($seed))->build();
    }

    public function getRateLimitExceptionExample(): BadRequestException
    {
        return new BadRequestException('Too many requests', 429);
    }
}
