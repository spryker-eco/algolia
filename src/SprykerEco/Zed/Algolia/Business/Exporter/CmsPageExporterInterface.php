<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Exporter;

use Generated\Shared\Transfer\AlgoliaExportCriteriaTransfer;
use Generated\Shared\Transfer\AlgoliaExportResultTransfer;
use Symfony\Component\Console\Output\OutputInterface;

interface CmsPageExporterInterface
{
    /**
     * @param \Symfony\Component\Console\Output\OutputInterface|null $output
     *
     * @return \Generated\Shared\Transfer\AlgoliaExportResultTransfer
     */
    public function exportCmsPages(
        AlgoliaExportCriteriaTransfer $criteriaTransfer,
        ?OutputInterface $output = null
    ): AlgoliaExportResultTransfer;
}
