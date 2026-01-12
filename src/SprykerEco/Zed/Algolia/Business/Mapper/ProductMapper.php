<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Mapper;

use ArrayObject;
use Generated\Shared\Transfer\AlgoliaConfigTransfer;
use Generated\Shared\Transfer\AlgoliaProductMetadataTransfer;
use Generated\Shared\Transfer\AlgoliaProductObjectTransfer;
use Generated\Shared\Transfer\AlgoliaProductPriceTransfer;
use Generated\Shared\Transfer\AlgoliaProductTransfer;
use Generated\Shared\Transfer\LocalizedAttributesTransfer;
use Generated\Shared\Transfer\LocalizedUrlTransfer;
use Generated\Shared\Transfer\NodeTransfer;
use Generated\Shared\Transfer\ProductConcreteTransfer;
use Generated\Shared\Transfer\ProductImageSetTransfer;
use Generated\Shared\Transfer\ProductImageTransfer;
use Generated\Shared\Transfer\ProductOfferTransfer;
use Spryker\Shared\Log\LoggerTrait;

class ProductMapper implements ProductMapperInterface
{
    use LoggerTrait;

    /**
     * {@inheritDoc}
     *
     * @param array<string, array<string, array<int, \Generated\Shared\Transfer\AlgoliaProductTransfer>>> $indexedAlgoliaProductTransfersArray
     *
     * @return array<string, array<string, array<int, \Generated\Shared\Transfer\AlgoliaProductTransfer>>>
     */
    public function mapProductConcreteToAlgoliaProductTransfersArrayIndexedByStoreAndLocale(
        ProductConcreteTransfer $productConcreteTransfer,
        array $indexedAlgoliaProductTransfersArray
    ): array {
        $storeToLocaleIndices = $this->getStoreToLocaleIndices($productConcreteTransfer);

        foreach ($storeToLocaleIndices as $storeName => $storeToLocaleIndex) {
            foreach ($storeToLocaleIndex as $locale => $algoliaProductTransfer) {
                $indexedAlgoliaProductTransfersArray[$storeName][$locale][] = $this->mapProductConcreteToAlgoliaProductTransfer(
                    $productConcreteTransfer,
                    $storeName,
                    $locale,
                );
            }
        }

        return $indexedAlgoliaProductTransfersArray;
    }

    /**
     * {@inheritDoc}
     *
     * @param array<\Generated\Shared\Transfer\AlgoliaProductTransfer> $algoliaProductTransfers

     * @return array<array<string, mixed>>
     */
    public function mapAlgoliaProductTransfersArrayToAlgoliaObjectArray(array $algoliaProductTransfers, AlgoliaConfigTransfer $algoliaConfigTransfer): array
    {
        return array_map(function (AlgoliaProductTransfer $algoliaProductTransfer) use ($algoliaConfigTransfer) {
            $arrayData = $algoliaProductTransfer->getObjectOrFail()->modifiedToArray();
            $arrayData['objectID'] = $arrayData['object_id'];
            unset($arrayData['object_id']);

            if ($algoliaConfigTransfer->getProductsWithoutPrice()) {
                unset($arrayData['concrete_prices']);
                unset($arrayData['prices']);
            }

            return $arrayData;
        }, $algoliaProductTransfers);
    }

    /**
     * @return array
     */
    protected function getStoreToLocaleIndices(ProductConcreteTransfer $productConcreteTransfer): array
    {
        $storeToLocaleIndices = [];
        $storeTransfers = $productConcreteTransfer->getStores();
        $locales = $this->getLocales($productConcreteTransfer);

        foreach ($storeTransfers as $storeTransfer) {
            $storeToLocaleIndices[$storeTransfer->getName()] = array_fill_keys(
                array_intersect(array_values($storeTransfer->getAvailableLocaleIsoCodes()), $locales),
                null,
            );
        }

        return $storeToLocaleIndices;
    }

    /**
     * @return list<string>
     */
    protected function getLocales(ProductConcreteTransfer $productConcreteTransfer): array
    {
        $localesFromAttributes = array_map(function (LocalizedAttributesTransfer $localizedAttributeTransfer) {
            return $localizedAttributeTransfer->getLocaleOrFail()->getLocaleNameOrFail();
        }, $productConcreteTransfer->getLocalizedAttributes()->getArrayCopy());

        $localesFromImageSets = array_map(function (ProductImageSetTransfer $productImageSetTransfer) {
            // imagesSet could have no locale - default for all locales.
            if ($productImageSetTransfer->getLocale() === null) {
                return null;
            }

            return $productImageSetTransfer->getLocaleOrFail()->getLocaleNameOrFail();
        }, $productConcreteTransfer->getImageSets()->getArrayCopy());

        $localesFromUrls = [];
        if ($productConcreteTransfer->getUrl()) {
            $localesFromUrls = array_map(function (LocalizedUrlTransfer $urlTransfer) {
                return $urlTransfer->getLocaleOrFail()->getLocaleNameOrFail();
            }, $productConcreteTransfer->getUrl()->getUrls()->getArrayCopy());
        }

        return array_unique(array_merge($localesFromAttributes, $localesFromImageSets, $localesFromUrls));
    }

    protected function mapProductConcreteToAlgoliaProductTransfer(
        ProductConcreteTransfer $productConcreteTransfer,
        string $storeName,
        string $locale
    ): AlgoliaProductTransfer {
        $algoliaProductMetadataTransfer = (new AlgoliaProductMetadataTransfer())
            ->setLocale($locale);

        $images = $this->getImageUrlsForLocale($productConcreteTransfer, $locale);

        $algoliaObjectTransfer = (new AlgoliaProductObjectTransfer())
            ->setObjectId($productConcreteTransfer->getSku())
            ->setName($productConcreteTransfer->getName())
            ->setProductAbstractSku($productConcreteTransfer->getAbstractSku())
            ->setSku($productConcreteTransfer->getSku())
            ->setUrl($this->getProductUrlForLocale($productConcreteTransfer, $locale))
            ->setImages($images)
            ->setAttributes($productConcreteTransfer->getAttributes())
            ->setPrices($this->getPricesIndexedByCurrency($productConcreteTransfer->getProductAbstractPrices(), $storeName))
            ->setConcretePrices($this->getPricesIndexedByCurrency($productConcreteTransfer->getPrices(), $storeName))
            ->setRating($this->getRoundedRating($productConcreteTransfer))
            ->setMerchantName($this->getMerchantNames($productConcreteTransfer, $storeName))
            ->setMerchantReference($this->getMerchantReferences($productConcreteTransfer, $storeName))
            ->setLabel($this->getLabelsForLocale($productConcreteTransfer, $locale))
            ->setHierarchicalCategories($this->getHierarchicalCategoriesForLocale($productConcreteTransfer, $locale))
            ->setSearchMetadata($productConcreteTransfer->getSearchMetadata());

        $algoliaObjectTransfer->setCategory($this->getParentCategoryNames(
            $productConcreteTransfer->getRelatedCategoryTreeNodes(),
            $locale,
        ));

        $localizedAttributes = $this->getLocalizedAttributesForLocale(
            $productConcreteTransfer->getLocalizedAttributes()->getArrayCopy(),
            $locale,
        );

        $abstractLocalizedAttributes = $this->getLocalizedAttributesForLocale(
            $productConcreteTransfer->getAbstractLocalizedAttributes()->getArrayCopy(),
            $locale,
        );

        $algoliaObjectTransfer
            ->setAttributes(array_merge($algoliaObjectTransfer->getAttributes(), $localizedAttributes->getAttributes()))
            ->setDescription($localizedAttributes->getDescription())
            ->setKeywords($localizedAttributes->getMetaKeywords())
            ->setName($localizedAttributes->getName())
            ->setAbstractName($abstractLocalizedAttributes->getName());

        return (new AlgoliaProductTransfer())
            ->setMetadata($algoliaProductMetadataTransfer)
            ->setObject($algoliaObjectTransfer);
    }

    /**
     * @param array $localizedAttributesCollection
     */
    protected function getLocalizedAttributesForLocale(array $localizedAttributesCollection, string $locale): LocalizedAttributesTransfer
    {
        foreach ($localizedAttributesCollection as $localizedAttributes) {
            if (!$localizedAttributes->getLocale()) {
                continue;
            }
            if ($localizedAttributes->getLocale()->getLocaleName() === $locale) {
                return $localizedAttributes;
            }
        }

        return new LocalizedAttributesTransfer();
    }

    protected function getProductUrlForLocale(ProductConcreteTransfer $productConcreteTransfer, string $locale): string
    {
        if (!$productConcreteTransfer->getUrl()) {
            return '';
        }

        foreach ($productConcreteTransfer->getUrl()->getUrls() as $url) {
            if (!$url->getLocale()) {
                continue;
            }
            if ($url->getLocale()->getLocaleName() === $locale) {
                return $url->getUrl() ?? '';
            }
        }

        return '';
    }

    /**
     * @return array<mixed>
     */
    protected function getImageUrlsForLocale(ProductConcreteTransfer $productConcreteTransfer, string $locale): array
    {
        $images = [];

        foreach ($productConcreteTransfer->getImageSets() as $imageSet) {
            // when locale is empty it means the imagesSet has to be used (default)
            if ($imageSet->getLocale() === null || $imageSet->getLocale()->getLocaleName() === $locale) {
                $images[$imageSet->getName()] = array_map(function (ProductImageTransfer $productImageTransfer) {
                    return [
                        'small' => $productImageTransfer->getExternalUrlSmall(),
                        'large' => $productImageTransfer->getExternalUrlLarge(),
                    ];
                }, $imageSet->getProductImages()->getArrayCopy());
            }
        }

        return $images;
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\PriceProductTransfer> $priceProductTransfers

     * @return \ArrayObject<string, \Generated\Shared\Transfer\AlgoliaProductPriceTransfer>
     */
    protected function getPricesIndexedByCurrency(ArrayObject $priceProductTransfers, string $storeName): ArrayObject
    {
        /**
         * @var \ArrayObject<string, \Generated\Shared\Transfer\AlgoliaProductPriceTransfer> $pricesPerCurrency
         */
        $pricesPerCurrency = new ArrayObject();

        $priceProductIds = [];

        /** @var \Generated\Shared\Transfer\PriceProductTransfer $price */
        foreach ($priceProductTransfers as $price) {
            if ($price->getMoneyValue()->getStore()->getName() !== $storeName) {
                continue;
            }

            $currencyCode = strtolower($price->getMoneyValue()->getCurrency()->getCode());
            $algoliaProductPriceTransfer = (new AlgoliaProductPriceTransfer())
                ->setGross($price->getMoneyValue()->getGrossAmount())
                ->setNet($price->getMoneyValue()->getNetAmount());

            $pricesPerCurrency[$currencyCode] = $algoliaProductPriceTransfer;
            $priceProductIds[] = $price->getIdPriceProduct();
        }

        if (count(array_unique($priceProductIds)) > count($pricesPerCurrency)) {
            $this->getLogger()->warning('Count of unique PriceProduct transfers does not match expectations', ['priceProductIds' => $priceProductIds]);
        }

        return $pricesPerCurrency;
    }

    protected function getRoundedRating(ProductConcreteTransfer $productConcreteTransfer): float
    {
        return round((float)$productConcreteTransfer->getRating(), 1);
    }

    /**
     * @return array<string>
     */
    protected function getMerchantNames(ProductConcreteTransfer $productConcreteTransfer, string $storeName): array
    {
        $merchantNames = [];
        foreach ($productConcreteTransfer->getOffers() as $offer) {
            if ($this->offerBelongsToStore($offer, $storeName) && $offer->getIsActive()) {
                $merchantNames[] = $offer->getMerchantName();
            }
        }

        return $merchantNames;
    }

    /**
     * @return array<string>
     */
    protected function getMerchantReferences(ProductConcreteTransfer $productConcreteTransfer, string $storeName): array
    {
        $merchantReferences = [];
        foreach ($productConcreteTransfer->getOffers() as $offer) {
            if ($this->offerBelongsToStore($offer, $storeName) && $offer->getIsActive()) {
                $merchantReferences[] = $offer->getMerchantReference();
            }
        }

        return $merchantReferences;
    }

    protected function offerBelongsToStore(ProductOfferTransfer $productOfferTransfer, string $storeReference): bool
    {
        foreach ($productOfferTransfer->getStores() as $store) {
            if ($store->getName() === $storeReference) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string>
     */
    protected function getLabelsForLocale(ProductConcreteTransfer $productConcreteTransfer, string $locale): array
    {
        $productLabelNames = [];

        foreach ($productConcreteTransfer->getProductLabels() as $productLabel) {
            $productLabelName = '';

            foreach ($productLabel->getLocalizedAttributesCollection() as $localizedAttributesTransfer) {
                if ($localizedAttributesTransfer->getLocale()->getLocaleName() === $locale) {
                    $productLabelName = $localizedAttributesTransfer->getName();

                    break;
                }
            }

            if (!$productLabelName) {
                $productLabelName = $productLabel->getName();
            }

            $productLabelNames[] = $productLabelName;
        }

        return $productLabelNames;
    }

    /**
     * @return array<string> Array of category names grouped by hierarchy (see InstantSearch.js format)
     */
    protected function getHierarchicalCategoriesForLocale(ProductConcreteTransfer $productConcreteTransfer, string $locale): array
    {
        $hierarchicalCategories = $this->collectCategoryNamesFromTree(
            [],
            $productConcreteTransfer->getRelatedCategoryTreeNodes(),
            $locale,
        );

        return $hierarchicalCategories;
    }

    /**
     * @param array<string, string|array<string>> $categoryNames
     * @param \ArrayObject<int, \Generated\Shared\Transfer\NodeTransfer> $relatedCategoryTreeNodes

     * @return array<string, mixed>
     */
    protected function collectCategoryNamesFromTree(
        array $categoryNames,
        ArrayObject $relatedCategoryTreeNodes,
        string $locale,
        int $level = 0,
        string $namePrefix = ''
    ): array {
        $nestingLevelKey = sprintf('lvl%d', $level);
        /** @var \Generated\Shared\Transfer\NodeTransfer $relatedCategoryTreeNode */
        foreach ($relatedCategoryTreeNodes as $relatedCategoryTreeNode) {
            $currentCategoryName = $namePrefix . $this->getLocalizedCategoryName($relatedCategoryTreeNode, $locale);

            if (isset($categoryNames[$nestingLevelKey])) {
                $categoryNames = $this->updateCategoryNamesLevel(
                    $categoryNames,
                    $currentCategoryName,
                    $nestingLevelKey,
                );

                continue;
            }

            $categoryNames[$nestingLevelKey] = $currentCategoryName;

            if ($relatedCategoryTreeNode->getChildrenNodes() && $relatedCategoryTreeNode->getChildrenNodes()->getNodes()->count()) {
                $categoryNames = $this->collectCategoryNamesFromTree(
                    $categoryNames,
                    $relatedCategoryTreeNode->getChildrenNodes()->getNodes(),
                    $locale,
                    $level + 1,
                    $currentCategoryName . ' > ',
                );
            }
        }

        return $categoryNames;
    }

    /**
     * @param array<string, mixed> $categoryNames

     * @return array<string, string>
     */
    protected function updateCategoryNamesLevel(array $categoryNames, string $categoryName, string $levelKey): array
    {
        if (is_string($categoryNames[$levelKey])) {
            $categoryNames[$levelKey] = [$categoryNames[$levelKey], $categoryName];
        }

        return $categoryNames;
    }

    protected function getLocalizedCategoryName(NodeTransfer $nodeTransfer, string $locale): string
    {
        $categoryName = $nodeTransfer->getCategory()->getCategoryKey();
        foreach ($nodeTransfer->getCategory()->getLocalizedAttributes() as $categoryLocalizedAttribute) {
            if (!$categoryLocalizedAttribute->getLocale()) {
                continue;
            }
            if ($categoryLocalizedAttribute->getLocale()->getLocaleName() === $locale) {
                $categoryName = $categoryLocalizedAttribute->getName();
            }
        }

        return $categoryName;
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\NodeTransfer> $relatedCategoryTreeNodes
     * @param array<int, string> $parentCategoryNames
     *
     * @return array<int, string>
     */
    protected function getParentCategoryNames(ArrayObject $relatedCategoryTreeNodes, string $locale, array $parentCategoryNames = []): array
    {
        foreach ($relatedCategoryTreeNodes as $categoryTreeNodeTransfer) {
            if ($categoryTreeNodeTransfer->getCategory()->getIsSearchable()) {
                $parentCategoryNames[] = $this->getLocalizedCategoryName($categoryTreeNodeTransfer, $locale);
            }

            if ($categoryTreeNodeTransfer->getChildrenNodes() && $categoryTreeNodeTransfer->getChildrenNodes()->getNodes()->count()) {
                $parentCategoryNames = $this->getParentCategoryNames($categoryTreeNodeTransfer->getChildrenNodes()->getNodes(), $locale, $parentCategoryNames);
            }
        }

        return $parentCategoryNames;
    }
}
