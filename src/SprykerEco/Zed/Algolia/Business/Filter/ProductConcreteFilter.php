<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Filter;

use ArrayObject;
use Generated\Shared\Transfer\AlgoliaConfigTransfer;
use Generated\Shared\Transfer\ProductConcreteTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Spryker\Zed\Store\Business\StoreFacadeInterface;

class ProductConcreteFilter implements ProductConcreteFilterInterface
{
    /**
     * @var array<\Generated\Shared\Transfer\StoreTransfer>|null
     */
    protected ?array $storesCache = null;

    public function __construct(protected StoreFacadeInterface $storeFacade)
    {
    }

    /**
     * @var string
     */
    public const STATUS_APPROVED = 'approved';

    /**
     * @param \ArrayObject<\Generated\Shared\Transfer\ProductConcreteTransfer> $productsConcrete

     * @return \ArrayObject<\Generated\Shared\Transfer\ProductConcreteTransfer>
     */
    public function filterIndexableProductsConcrete(
        ArrayObject $productsConcrete,
        AlgoliaConfigTransfer $algoliaConfigTransfer
    ): ArrayObject {
        $filteredProductsConcrete = new ArrayObject();

        foreach ($productsConcrete as $productConcrete) {
            $clonedProductConcrete = clone $productConcrete;
            $clonedProductConcrete->setStores((new ArrayObject()));

            foreach ($productConcrete->getStores() as $storeTransfer) {
                if ($this->canBeIndexed($productConcrete, $storeTransfer, $algoliaConfigTransfer)) {
                    $clonedProductConcrete->addStores($storeTransfer);
                }
            }

            if (count($clonedProductConcrete->getStores())) {
                $filteredProductsConcrete->append($clonedProductConcrete);
            }
        }

        return $filteredProductsConcrete;
    }

    /**
     * @param \ArrayObject<\Generated\Shared\Transfer\ProductConcreteTransfer> $productsConcrete

     * @return \ArrayObject<\Generated\Shared\Transfer\ProductConcreteTransfer>
     */
    public function filterNonIndexableProductsConcrete(
        ArrayObject $productsConcrete,
        AlgoliaConfigTransfer $algoliaConfigTransfer
    ): ArrayObject {
        $filteredProductsConcrete = new ArrayObject();

        if ($this->storesCache === null) {
            $this->storesCache = $this->storeFacade->getAllStores();
        }

        foreach ($productsConcrete as $productConcrete) {
            $clonedProductConcrete = clone $productConcrete;
            $clonedProductConcrete->setStores((new ArrayObject()));

            $productStoreNames = $this->getStoreNamesFromProduct($productConcrete);

            foreach ($this->storesCache as $storeCacheTransfer) {
                if (!in_array($storeCacheTransfer->getName(), $productStoreNames)) {
                    $clonedProductConcrete->addStores($storeCacheTransfer);

                    continue;
                }

                $productStoreTransfer = $this->findStoreByName($productConcrete, $storeCacheTransfer->getName());

                if (!$this->canBeIndexed($productConcrete, $productStoreTransfer, $algoliaConfigTransfer) || $this->hasAtLeastOneNonSearchableLocale($productConcrete)) {
                    $clonedProductConcrete->addStores($productStoreTransfer);
                }
            }

            if (count($clonedProductConcrete->getStores())) {
                $filteredProductsConcrete->append($clonedProductConcrete);
            }
        }

        return $filteredProductsConcrete;
    }

    protected function canBeIndexed(
        ProductConcreteTransfer $productConcreteTransfer,
        StoreTransfer $storeTransfer,
        AlgoliaConfigTransfer $algoliaConfigTransfer
    ): bool {
        return $productConcreteTransfer->getIsActive()
            && ($productConcreteTransfer->getApprovalStatus() === null
                || $productConcreteTransfer->getApprovalStatus() === static::STATUS_APPROVED)
            && $this->assertPricesAreValid($productConcreteTransfer, $storeTransfer, $algoliaConfigTransfer)
            && $this->hasAtLeastOneSearchableLocale($productConcreteTransfer);
    }

    protected function assertPricesAreValid(
        ProductConcreteTransfer $productConcreteTransfer,
        StoreTransfer $storeTransfer,
        AlgoliaConfigTransfer $algoliaConfigTransfer
    ): bool {
        if ($algoliaConfigTransfer->getIsProductPriceSynced()) {
            return true;
        }

        foreach ($productConcreteTransfer->getProductAbstractPrices() as $productAbstractPrice) {
            if (
                $productAbstractPrice->getMoneyValue()
                && $productAbstractPrice->getMoneyValue()->getStore()->getName() === $storeTransfer->getName()
                && ($productAbstractPrice->getMoneyValue()->getGrossAmount() !== null || $productAbstractPrice->getMoneyValue()->getNetAmount() !== null)
            ) {
                return true;
            }
        }

        return false;
    }

    protected function hasAtLeastOneSearchableLocale(ProductConcreteTransfer $productConcreteTransfer): bool
    {
        foreach ($productConcreteTransfer->getLocalizedAttributes() as $localizedAttribute) {
            if ($localizedAttribute->getIsSearchable()) {
                return true;
            }
        }

        return false;
    }

    protected function hasAtLeastOneNonSearchableLocale(ProductConcreteTransfer $productConcreteTransfer): bool
    {
        foreach ($productConcreteTransfer->getLocalizedAttributes() as $localizedAttribute) {
            if (!$localizedAttribute->getIsSearchable()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string>
     */
    protected function getStoreNamesFromProduct(ProductConcreteTransfer $productConcreteTransfer): array
    {
        $storeNames = [];

        foreach ($productConcreteTransfer->getStores() as $storeTransfer) {
            $storeNames[] = $storeTransfer->getName();
        }

        return $storeNames;
    }

    protected function findStoreByName(ProductConcreteTransfer $productConcreteTransfer, string $storeName): ?StoreTransfer
    {
        foreach ($productConcreteTransfer->getStores() as $storeTransfer) {
            if ($storeTransfer->getName() === $storeName) {
                return $storeTransfer;
            }
        }

        return null;
    }
}
