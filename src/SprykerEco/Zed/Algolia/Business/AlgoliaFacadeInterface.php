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
use Generated\Shared\Transfer\CmsPageUnpublishedTransfer;
use Generated\Shared\Transfer\ProductDeletedTransfer;
use Generated\Shared\Transfer\SearchRequestTransfer;
use Generated\Shared\Transfer\SearchResponseTransfer;
use Generated\Shared\Transfer\SuggestionsSearchResponseTransfer;
use Symfony\Component\Console\Output\OutputInterface;

interface AlgoliaFacadeInterface
{
    /**
     * Specification:
     * - Check the validity of provided API credentials (tries to list indices).
     * - Responds with validator response transfer with success on failure
     *
     * @api
     */
    public function validateApiCredentials(
        AlgoliaConfigTransfer $algoliaConfigTransfer
    ): AlgoliaApiCredentialsValidationTransfer;

    /**
     * Specification:
     * - Exports products to Algolia using criteria-based approach.
     * - Processes products in chunks using pagination.
     * - Uses the chunk size from AlgoliaExportCriteriaTransfer.
     * - Filters by locale if provided in criteria.
     * - Displays chunk progress to OutputInterface if provided.
     * - Returns detailed export statistics including total count, exported count, and failed count.
     *
     * @api
     *
     * @param \Symfony\Component\Console\Output\OutputInterface|null $output
     *
     * @return \Generated\Shared\Transfer\AlgoliaExportResultTransfer
     */
    public function exportProducts(
        AlgoliaExportCriteriaTransfer $criteriaTransfer,
        ?OutputInterface $output = null
    ): AlgoliaExportResultTransfer;

    /**
     * Specification:
     * - Transforms data to the appropriate format.
     * - Prepares a request for Algolia API.
     * - Sends the prepared request to Algolia API to create a new entity (saveObjects action).
     * - Creates an index in Algolia if the one does not exist.
     *
     * @api
     *
     * @param \ArrayObject<\Generated\Shared\Transfer\ProductConcreteTransfer> $productConcreteTransfers
     *
     * @return \Generated\Shared\Transfer\AlgoliaResponseTransfer
     */
    public function updateProducts(ArrayObject $productConcreteTransfers): AlgoliaResponseTransfer;

    /**
     * Specification:
     * - Gets a list of existing indices.
     * - Prepares a request for Algolia API.
     * - Sends the prepared request to Algolia API to delete an existing entity (deleteObject action) in all indices.
     * - Stops and returns response on first error encountered
     *
     * @api
     */
    public function deleteProduct(ProductDeletedTransfer $productDeletedTransfer): void;

    /**
     * Specification:
     * - Performs a search request on Algolia app
     *
     * @api
     *
     * @throws \SprykerEco\Zed\Algolia\Business\Api\Exception\AlgoliaConfigNotFoundException
     * @throws \Algolia\AlgoliaSearch\Exceptions\NotFoundException
     */
    public function search(SearchRequestTransfer $searchRequestTransfer): SearchResponseTransfer;

    /**
     * Specification:
     * - Performs a multiple indices search request on Algolia app.
     * - Uses search index to collect matches.
     * - Uses suggestions index to search for completions.
     *
     * @api
     *
     * @throws \SprykerEco\Zed\Algolia\Business\Api\Exception\AlgoliaConfigNotFoundException
     * @throws \Algolia\AlgoliaSearch\Exceptions\NotFoundException
     */
    public function searchSuggestions(SearchRequestTransfer $searchRequestTransfer): SuggestionsSearchResponseTransfer;

    /**
     * Specification:
     * - Publishes CMS page data to Algolia.
     *
     * @api
     */
    public function publishCmsPage(CmsPagePublishedTransfer $cmsPagePublishedTransfer): AlgoliaResponseTransfer;

    /**
     * Specification:
     * - Deletes CMS page data from Algolia.
     *
     * @api
     */
    public function deleteCmsPage(CmsPageUnpublishedTransfer $cmsPageUnpublishedTransfer): void;

    /**
     * Specification:
     * - Exports all active searchable CMS pages to Algolia.
     * - Processes CMS pages in chunks using Propel queries.
     * - Uses the chunk size from AlgoliaExportCriteriaTransfer.
     * - Filters by store name if provided in criteria (uses spy_cms_page_store table).
     * - Filters by locale if provided in criteria (future support).
     * - Displays chunk progress to OutputInterface if provided.
     * - Returns detailed export statistics including total count, exported count, and failed count.
     *
     * @api
     *
     * @param \Symfony\Component\Console\Output\OutputInterface|null $output
     *
     * @return \Generated\Shared\Transfer\AlgoliaExportResultTransfer
     */
    public function exportCmsPages(
        AlgoliaExportCriteriaTransfer $criteriaTransfer,
        ?OutputInterface $output = null
    ): AlgoliaExportResultTransfer;
}
