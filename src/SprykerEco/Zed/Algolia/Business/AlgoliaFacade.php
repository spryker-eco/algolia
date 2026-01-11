<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business;

use Generated\Shared\Transfer\AlgoliaApiCredentialsValidationTransfer;
use Generated\Shared\Transfer\AlgoliaConfigTransfer;
use Generated\Shared\Transfer\AlgoliaResponseTransfer;
use Generated\Shared\Transfer\CmsPagePublishedTransfer;
use Generated\Shared\Transfer\CmsPageUnpublishedTransfer;
use Generated\Shared\Transfer\ProductCreatedTransfer;
use Generated\Shared\Transfer\ProductDeletedTransfer;
use Generated\Shared\Transfer\ProductExportedTransfer;
use Generated\Shared\Transfer\ProductUpdatedTransfer;
use Generated\Shared\Transfer\SearchRequestTransfer;
use Generated\Shared\Transfer\SearchResponseTransfer;
use Generated\Shared\Transfer\SuggestionsSearchResponseTransfer;
use Spryker\Zed\Kernel\Business\AbstractFacade;

/**
 * @method \SprykerEco\Zed\Algolia\Business\AlgoliaBusinessFactory getFactory()
 */
class AlgoliaFacade extends AbstractFacade implements AlgoliaFacadeInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function validateApiCredentials(
        AlgoliaConfigTransfer $algoliaConfigTransfer
    ): AlgoliaApiCredentialsValidationTransfer {
        return $this->getFactory()->createApiCredentialsValidator()->validate($algoliaConfigTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function exportProducts(ProductExportedTransfer $productExportedTransfer): AlgoliaResponseTransfer
    {
        return $this->getFactory()
            ->createProductExporter()
            ->exportProducts($productExportedTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function createProducts(ProductCreatedTransfer $productCreatedTransfer): AlgoliaResponseTransfer
    {
        return $this->getFactory()
            ->createProductCreator()
            ->createProducts($productCreatedTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function updateProducts(ProductUpdatedTransfer $productUpdatedTransfer): AlgoliaResponseTransfer
    {
        return $this->getFactory()
            ->createProductUpdater()
            ->updateProducts($productUpdatedTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function deleteProduct(ProductDeletedTransfer $productDeletedTransfer): void
    {
        $this->getFactory()
            ->createProductDeleter()
            ->deleteProduct($productDeletedTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function search(SearchRequestTransfer $searchRequestTransfer): SearchResponseTransfer
    {
        return $this->getFactory()->createSearcher()->search($searchRequestTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function searchSuggestions(SearchRequestTransfer $searchRequestTransfer): SuggestionsSearchResponseTransfer
    {
        return $this->getFactory()->createSuggestionsSearcher()->searchSuggestions($searchRequestTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function publishedCmsPage(CmsPagePublishedTransfer $cmsPagePublishedTransfer): AlgoliaResponseTransfer
    {
        return $this->getFactory()
            ->createCmsPagePublisher()
            ->publishCmsPages($cmsPagePublishedTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function deleteCmsPage(CmsPageUnpublishedTransfer $cmsPageUnpublishedTransfer): void
    {
        $this->getFactory()
            ->createCmsPageDeleter()
            ->deleteCmsPage($cmsPageUnpublishedTransfer);
    }
}
