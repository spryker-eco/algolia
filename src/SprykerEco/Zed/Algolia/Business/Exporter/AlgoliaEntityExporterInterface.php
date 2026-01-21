<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Exporter;

use Generated\Shared\Transfer\AlgoliaExportCriteriaTransfer;
use Generated\Shared\Transfer\AlgoliaExportResultTransfer;
use Symfony\Component\Console\Output\OutputInterface;

interface AlgoliaEntityExporterInterface
{
    /**
     * @param \Generated\Shared\Transfer\AlgoliaExportCriteriaTransfer $criteriaTransfer
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return \Generated\Shared\Transfer\AlgoliaExportResultTransfer
     */
    public function exportEntities(
        AlgoliaExportCriteriaTransfer $criteriaTransfer,
        OutputInterface $output
    ): AlgoliaExportResultTransfer;

    /**
     * @return array<string>
     */
    public function getAvailableEntityTypes(): array;
}
