<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business;

use ArrayObject;
use Generated\Shared\Transfer\AlgoliaApiCredentialsValidationTransfer;
use Generated\Shared\Transfer\AlgoliaConfigTransfer;
use Generated\Shared\Transfer\AlgoliaExportCriteriaTransfer;
use Generated\Shared\Transfer\AlgoliaExportResultTransfer;
use Generated\Shared\Transfer\AlgoliaResponseTransfer;
use Generated\Shared\Transfer\CmsPagePublishedTransfer;
use Generated\Shared\Transfer\ProductDeletedTransfer;
use Generated\Shared\Transfer\SearchRequestTransfer;
use Generated\Shared\Transfer\SearchResponseTransfer;
use Generated\Shared\Transfer\SuggestionsSearchResponseTransfer;
use Spryker\Zed\Kernel\Business\AbstractFacade;
use Symfony\Component\Console\Output\OutputInterface;

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
     *
     * @param \Symfony\Component\Console\Output\OutputInterface|null $output
     */
    public function exportProducts(
        AlgoliaExportCriteriaTransfer $criteriaTransfer,
        ?OutputInterface $output = null
    ): AlgoliaExportResultTransfer {
        return $this->getFactory()
            ->createProductExporter()
            ->exportProducts($criteriaTransfer, $output);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \ArrayObject<\Generated\Shared\Transfer\ProductConcreteTransfer> $productConcreteTransfers
     */
    public function updateProducts(ArrayObject $productConcreteTransfers): AlgoliaResponseTransfer
    {
        return $this->getFactory()
            ->createProductUpdater()
            ->updateProducts($productConcreteTransfers);
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
    public function publishCmsPage(CmsPagePublishedTransfer $cmsPagePublishedTransfer): AlgoliaResponseTransfer
    {
        return $this->getFactory()
            ->createCmsPagePublisher()
            ->publishCmsPage($cmsPagePublishedTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param array<int> $cmsPageIds
     */
    public function deleteCmsPages(array $cmsPageIds): void
    {
        $this->getFactory()
            ->createCmsPageDeleter()
            ->deleteCmsPagesByIds($cmsPageIds);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Symfony\Component\Console\Output\OutputInterface|null $output
     */
    public function exportCmsPages(
        AlgoliaExportCriteriaTransfer $criteriaTransfer,
        ?OutputInterface $output = null
    ): AlgoliaExportResultTransfer {
        return $this->getFactory()
            ->createCmsPageExporter()
            ->exportCmsPages($criteriaTransfer, $output);
    }
}
