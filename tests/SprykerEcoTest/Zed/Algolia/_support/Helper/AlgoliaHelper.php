<?php

/**
 * Copyright © 2022-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Zed\Algolia\Helper;

use Algolia\AlgoliaSearch\Exceptions\BadRequestException;
use ArrayObject;
use Codeception\Module;
use Generated\Shared\DataBuilder\AlgoliaApiCredentialsBuilder;
use Generated\Shared\DataBuilder\AlgoliaProductObjectBuilder;
use Generated\Shared\DataBuilder\CategoryBuilder;
use Generated\Shared\DataBuilder\CategoryLocalizedAttributesBuilder;
use Generated\Shared\DataBuilder\DisconnectParametersBuilder;
use Generated\Shared\DataBuilder\FacetCollectionBuilder;
use Generated\Shared\DataBuilder\FacetEntryBuilder;
use Generated\Shared\DataBuilder\FacetParametersBuilder;
use Generated\Shared\DataBuilder\LocaleBuilder;
use Generated\Shared\DataBuilder\LocalizedAttributesBuilder;
use Generated\Shared\DataBuilder\LocalizedUrlBuilder;
use Generated\Shared\DataBuilder\MessageAttributesBuilder;
use Generated\Shared\DataBuilder\MoneyValueBuilder;
use Generated\Shared\DataBuilder\NodeBuilder;
use Generated\Shared\DataBuilder\NodeCollectionBuilder;
use Generated\Shared\DataBuilder\PaginationEntryBuilder;
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
use Generated\Shared\DataBuilder\SearchRequestBuilder;
use Generated\Shared\DataBuilder\StoreBuilder;
use Generated\Shared\Transfer\AlgoliaApiCredentialsTransfer;
use Generated\Shared\Transfer\AlgoliaProductObjectTransfer;
use Generated\Shared\Transfer\DisconnectParametersTransfer;
use Generated\Shared\Transfer\FacetCollectionTransfer;
use Generated\Shared\Transfer\FacetEntryTransfer;
use Generated\Shared\Transfer\FacetParametersTransfer;
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
use Generated\Shared\Transfer\SearchRequestTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use SprykerEco\Shared\Algolia\Enum\AlgoliaEntityNameEnum;

class AlgoliaHelper extends Module
{
    /**
     * @param array $seed
     *
     * @return \Generated\Shared\Transfer\ProductConcreteTransfer
     */
    public function haveFullProductConcreteTransfer(
        array $seed = []
    ): ProductConcreteTransfer {
        $locale1Builder = new LocaleBuilder($seed[ProductConcreteTransfer::LOCALIZED_ATTRIBUTES][0][LocalizedAttributesTransfer::LOCALE] ?? []);
        $locale2Builder = new LocaleBuilder($seed[ProductConcreteTransfer::LOCALIZED_ATTRIBUTES][1][LocalizedAttributesTransfer::LOCALE] ?? []);

        $storeBuilder = (new StoreBuilder(
            $seed + [
                StoreTransfer::AVAILABLE_LOCALE_ISO_CODES => array_unique([
                    $locale1Builder->build()->getLocaleName(),
                    $locale2Builder->build()->getLocaleName(),
                ]),
            ],
        ));

        $category1Builder = (new CategoryBuilder())
            ->withAnotherLocalizedAttributes(
                (new CategoryLocalizedAttributesBuilder())
                    ->withLocale($locale1Builder),
            )
            ->withAnotherLocalizedAttributes(
                (new CategoryLocalizedAttributesBuilder())
                    ->withLocale($locale2Builder),
            );

        $category2Builder = (new CategoryBuilder())
            ->withAnotherLocalizedAttributes(
                (new CategoryLocalizedAttributesBuilder())
                    ->withLocale($locale1Builder),
            )
            ->withAnotherLocalizedAttributes(
                (new CategoryLocalizedAttributesBuilder())
                    ->withLocale($locale2Builder),
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
                    ->withLocale($locale2Builder),
            )
            ->withStores($storeBuilder)
            ->withAnotherProductLabel(
                (new ProductLabelBuilder())->withAnotherLocalizedAttributes(
                    (new ProductLabelLocalizedAttributesBuilder())->withAnotherLocale($locale1Builder),
                ),
            )
            ->withAnotherStores()
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
            ->addProductAbstractPrice($priceProductTransfer)
            ->setIsActive(true)
            ->setAttributes(['attribute1' => 'attributeValue1'])
            ->addLocalizedAttributes(
                (new LocalizedAttributesBuilder())
                    ->withLocale($locale1Builder)
                    ->build()
                    ->setAttributes([
                        'localizedAttribute1' => 'localizedAttribute1Value1',
                    ]),
            )
            ->addLocalizedAttributes(
                (new LocalizedAttributesBuilder())
                    ->withLocale($locale2Builder)
                    ->build()
                    ->setAttributes([
                        'localizedAttribute1' => 'localizedAttribute1Value2',
                    ]),
            )
            ->setUrl((new ProductUrlTransfer())
                ->addUrl((new LocalizedUrlBuilder())->withLocale($locale1Builder)->build())
                ->addUrl((new LocalizedUrlBuilder())->withLocale($locale2Builder)->build()))
            ->addRelatedCategoryTreeNode(
                (new NodeBuilder())->withCategory(
                    (new CategoryBuilder())
                        ->withAnotherLocalizedAttributes(
                            (new CategoryLocalizedAttributesBuilder())
                                ->withLocale($locale1Builder),
                        )
                        ->withAnotherLocalizedAttributes(
                            (new CategoryLocalizedAttributesBuilder())
                                ->withLocale($locale2Builder),
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

    /**
     * @param string $name
     * @param string $sku
     *
     * @return \Generated\Shared\Transfer\ProductConcreteTransfer
     */
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

    /**
     * @param string $name
     * @param string $sku
     *
     * @return \Generated\Shared\Transfer\ProductConcreteTransfer
     */
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
     *
     * @return \Generated\Shared\Transfer\AlgoliaProductObjectTransfer
     */
    public function haveAlgoliaObjectTransfer(array $seed = []): AlgoliaProductObjectTransfer
    {
        return (new AlgoliaProductObjectBuilder($seed))->build();
    }

    /**
     * @param \Generated\Shared\Transfer\ProductConcreteTransfer $productConcreteTransfer
     *
     * @return \Generated\Shared\Transfer\ProductExportedTransfer
     */
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
     *
     * @return \Generated\Shared\Transfer\ProductExportedTransfer
     */
    public function haveProductExportedTransfer(array $seed = []): ProductExportedTransfer
    {
        return (new ProductExportedBuilder($seed))->build();
    }

    /**
     * @param array $seed
     *
     * @return \Generated\Shared\Transfer\ProductCreatedTransfer
     */
    public function haveProductCreatedTransfer(array $seed = []): ProductCreatedTransfer
    {
        return (new ProductCreatedBuilder($seed))->build();
    }

    /**
     * @param array $seed
     *
     * @return \Generated\Shared\Transfer\ProductUpdatedTransfer
     */
    public function haveProductUpdatedTransfer(array $seed = []): ProductUpdatedTransfer
    {
        return (new ProductUpdatedBuilder($seed))->build();
    }

    /**
     * @param array $seed
     *
     * @return \Generated\Shared\Transfer\ProductDeletedTransfer
     */
    public function haveProductDeletedTransfer(array $seed = []): ProductDeletedTransfer
    {
        return (new ProductDeletedBuilder($seed))->build();
    }

    /**
     * @param array $seed
     *
     * @return \Generated\Shared\Transfer\AlgoliaApiCredentialsTransfer
     */
    public function haveAlgoliaApiCredentialsTransfer(array $seed = []): AlgoliaApiCredentialsTransfer
    {
        return (new AlgoliaApiCredentialsBuilder($seed))->build();
    }

    /**
     * @param array $seed
     *
     * @return \Generated\Shared\Transfer\MessageAttributesTransfer
     */
    public function haveMessageAttributesTransfer(array $seed = []): MessageAttributesTransfer
    {
        return (new MessageAttributesBuilder($seed))->build();
    }

    /**
     * @param array $seed
     *
     * @return \Generated\Shared\Transfer\DisconnectParametersTransfer
     */
    public function haveDisconnectParametersTransfer(array $seed = []): DisconnectParametersTransfer
    {
        return (new DisconnectParametersBuilder($seed))->build();
    }

    /**
     * @param array<mixed> $facets
     *
     * @return \Generated\Shared\Transfer\FacetCollectionTransfer
     */
    public function haveFacetCollectionTransfer(array $facets = []): FacetCollectionTransfer
    {
        return (new FacetCollectionBuilder())
            ->build()
            ->setFacets(new ArrayObject($facets));
    }

    /**
     * @param string $type
     * @param \Generated\Shared\Transfer\FacetParametersTransfer $facetParametersTransfer
     *
     * @return \Generated\Shared\Transfer\FacetEntryTransfer
     */
    public function haveFacetEntryTransfer(string $type, FacetParametersTransfer $facetParametersTransfer): FacetEntryTransfer
    {
        return (new FacetEntryBuilder())
            ->build()
            ->setType($type)
            ->setParameters($facetParametersTransfer);
    }

    /**
     * @param array $seed
     *
     * @return \Generated\Shared\Transfer\FacetParametersTransfer
     */
    public function haveFacetParametersTransfer(array $seed = []): FacetParametersTransfer
    {
        return (new FacetParametersBuilder($seed))
            ->build();
    }

    /**
     * @param array<mixed> $seed
     *
     * @return \Generated\Shared\Transfer\SearchRequestTransfer
     */
    public function haveSearchRequestTransfer(array $seed = []): SearchRequestTransfer
    {
        $paginationSeed = $seed['pagination_entry'] ?? [];
        $paginationEntryTransfer = (new PaginationEntryBuilder($paginationSeed))->build();
        $searchRequestTransfer = (new SearchRequestBuilder($seed))->build();

        if ($searchRequestTransfer->getStoreName() === null) {
            $searchRequestTransfer->setStoreName('DE');
        }
        if ($searchRequestTransfer->getLocale() === null) {
            $searchRequestTransfer->setLocale('en_US');
        }

        if ($searchRequestTransfer->getSourceIdentifier() === null) {
            $searchRequestTransfer->setSourceIdentifier(AlgoliaEntityNameEnum::PRODUCT->value);
        }

        return $searchRequestTransfer
            ->setPagination($paginationEntryTransfer)
            ->setSort();
    }

    /**
     * @return \Algolia\AlgoliaSearch\Exceptions\BadRequestException
     */
    public function getRateLimitExceptionExample(): BadRequestException
    {
        return new BadRequestException('Too many requests', 429);
    }
}
