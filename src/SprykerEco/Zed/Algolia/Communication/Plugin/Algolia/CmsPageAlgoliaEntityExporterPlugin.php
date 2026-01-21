<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Communication\Plugin\Algolia;

use Exception;
use Generated\Shared\Transfer\AlgoliaExportCriteriaTransfer;
use Generated\Shared\Transfer\AlgoliaExportResultTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use SprykerEco\Zed\Algolia\Dependency\Plugin\AlgoliaEntityExporterPluginInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @method \SprykerEco\Zed\Algolia\AlgoliaConfig getConfig()
 * @method \SprykerEco\Zed\Algolia\Business\AlgoliaFacadeInterface getFacade()
 * @method \SprykerEco\Zed\Algolia\Communication\AlgoliaCommunicationFactory getFactory()
 * @method \SprykerEco\Zed\Algolia\Business\AlgoliaBusinessFactory getBusinessFactory()
 */
class CmsPageAlgoliaEntityExporterPlugin extends AbstractPlugin implements AlgoliaEntityExporterPluginInterface
{
    /**
     * @var string
     */
    protected const ENTITY_TYPE = 'cms-page';

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return string
     */
    public function getEntityType(): string
    {
        return static::ENTITY_TYPE;
    }

    /**
     * {@inheritDoc}
     * - Retrieves all active searchable CMS page IDs.
     * - Processes CMS pages in chunks.
     * - Triggers Entity.spy_cms_page.export events for each chunk.
     * - Returns export statistics.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\AlgoliaExportCriteriaTransfer $criteriaTransfer
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return \Generated\Shared\Transfer\AlgoliaExportResultTransfer
     */
    public function export(
        AlgoliaExportCriteriaTransfer $criteriaTransfer,
        OutputInterface $output
    ): AlgoliaExportResultTransfer {
        $resultTransfer = (new AlgoliaExportResultTransfer())
            ->setEntityType(static::ENTITY_TYPE)
            ->setIsSuccessful(true);

        try {
            if ($criteriaTransfer->getIsDryRun()) {
                $output->writeln('<comment>DRY RUN: Would trigger CMS page export events</comment>');
                $resultTransfer->addMessage('Dry run completed - no actual export performed');

                return $resultTransfer;
            }

            $output->writeln('Triggering CMS page export events...');

            $this->getBusinessFactory()->getCmsFacade()->publishExportEvents();

            $output->writeln('<info>CMS page export events triggered successfully</info>');
            $resultTransfer->addMessage('CMS page export events triggered');
        } catch (Exception $exception) {
            $resultTransfer
                ->setIsSuccessful(false)
                ->addMessage(sprintf('Error: %s', $exception->getMessage()));
            $output->writeln(sprintf('<error>%s</error>', $exception->getMessage()));
        }

        return $resultTransfer;
    }
}
