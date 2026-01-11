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

interface AlgoliaFacadeInterface
{
    /**
     * Specification:
     * - Check the validity of provided API credentials (tries to list indices).
     * - Responds with validator response transfer with success on failure
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\AlgoliaConfigTransfer $algoliaConfigTransfer
     *
     * @return \Generated\Shared\Transfer\AlgoliaApiCredentialsValidationTransfer
     */
    public function validateApiCredentials(
        AlgoliaConfigTransfer $algoliaConfigTransfer
    ): AlgoliaApiCredentialsValidationTransfer;

    /**
     * Specification:
     * - Transforms data to the appropriate format.
     * - Prepares a request for Algolia API.
     * - Sends the prepared request to Algolia API to create a new entity (saveObjects action).
     * - Creates an index in Algolia if the one does not exist.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\ProductExportedTransfer $productExportedTransfer
     *
     * @return \Generated\Shared\Transfer\AlgoliaResponseTransfer
     */
    public function exportProducts(ProductExportedTransfer $productExportedTransfer): AlgoliaResponseTransfer;

    /**
     * Specification:
     * - Transforms data to the appropriate format.
     * - Prepares a request for Algolia API.
     * - Sends the prepared request to Algolia API to create a new entity (saveObjects action).
     * - Creates an index in Algolia if the one does not exist.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\ProductCreatedTransfer $productCreatedTransfer
     *
     * @return \Generated\Shared\Transfer\AlgoliaResponseTransfer
     */
    public function createProducts(ProductCreatedTransfer $productCreatedTransfer): AlgoliaResponseTransfer;

    /**
     * Specification:
     * - Transforms data to the appropriate format.
     * - Prepares a request for Algolia API.
     * - Sends the prepared request to Algolia API to create a new entity (saveObjects action).
     * - Creates an index in Algolia if the one does not exist.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\ProductUpdatedTransfer $productUpdatedTransfer
     *
     * @return \Generated\Shared\Transfer\AlgoliaResponseTransfer
     */
    public function updateProducts(ProductUpdatedTransfer $productUpdatedTransfer): AlgoliaResponseTransfer;

    /**
     * Specification:
     * - Gets a list of existing indices.
     * - Prepares a request for Algolia API.
     * - Sends the prepared request to Algolia API to delete an existing entity (deleteObject action) in all indices.
     * - Stops and returns response on first error encountered
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\ProductDeletedTransfer $productDeletedTransfer
     *
     * @return void
     */
    public function deleteProduct(ProductDeletedTransfer $productDeletedTransfer): void;

    /**
     * Specification:
     * - Performs a search request on Algolia app
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\SearchRequestTransfer $searchRequestTransfer
     *
     * @throws \SprykerEco\Zed\Algolia\Business\Api\Exception\AlgoliaConfigNotFoundException
     * @throws \Algolia\AlgoliaSearch\Exceptions\NotFoundException
     *
     * @return \Generated\Shared\Transfer\SearchResponseTransfer
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
     * @param \Generated\Shared\Transfer\SearchRequestTransfer $searchRequestTransfer
     *
     * @throws \SprykerEco\Zed\Algolia\Business\Api\Exception\AlgoliaConfigNotFoundException
     * @throws \Algolia\AlgoliaSearch\Exceptions\NotFoundException
     *
     * @return \Generated\Shared\Transfer\SuggestionsSearchResponseTransfer
     */
    public function searchSuggestions(SearchRequestTransfer $searchRequestTransfer): SuggestionsSearchResponseTransfer;

    /**
     * Specification:
     * - Publishes CMS page data to Algolia.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CmsPagePublishedTransfer $cmsPagePublishedTransfer
     *
     * @return \Generated\Shared\Transfer\AlgoliaResponseTransfer
     */
    public function publishedCmsPage(CmsPagePublishedTransfer $cmsPagePublishedTransfer): AlgoliaResponseTransfer;

    /**
     * Specification:
     * - Deletes CMS page data from Algolia.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CmsPageUnpublishedTransfer $cmsPageUnpublishedTransfer
     *
     * @return void
     */
    public function deleteCmsPage(CmsPageUnpublishedTransfer $cmsPageUnpublishedTransfer): void;
}
