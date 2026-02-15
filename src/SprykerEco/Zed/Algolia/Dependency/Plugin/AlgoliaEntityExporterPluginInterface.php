<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Dependency\Plugin;

use Generated\Shared\Transfer\AlgoliaExportCriteriaTransfer;
use Generated\Shared\Transfer\AlgoliaExportResultTransfer;
use Symfony\Component\Console\Output\OutputInterface;

interface AlgoliaEntityExporterPluginInterface
{
    /**
     * Specification:
     * - Returns the entity type name (e.g., 'product', 'cms-page', 'category').
     * - This name is used as the argument value in the console command.
     *
     * @api
     *
     * @return string
     */
    public function getEntityType(): string;

    /**
     * Specification:
     * - Exports entities to Algolia index based on the provided criteria.
     * - Uses criteria to filter entities by store, locale, etc.
     * - Processes entities in chunks based on criteria.chunkSize.
     * - Outputs progress information to the console.
     * - Returns export result with statistics.
     * - Skips actual export if criteria.isDryRun is true.
     *
     * @api
     *
     * @return \Generated\Shared\Transfer\AlgoliaExportResultTransfer
     */
    public function export(
        AlgoliaExportCriteriaTransfer $criteriaTransfer,
        OutputInterface $output
    ): AlgoliaExportResultTransfer;
}
