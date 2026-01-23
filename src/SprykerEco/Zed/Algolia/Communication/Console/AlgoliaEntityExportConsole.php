<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Communication\Console;

use Generated\Shared\Transfer\AlgoliaExportCriteriaTransfer;
use Generated\Shared\Transfer\AlgoliaExportResultTransfer;
use Spryker\Zed\Kernel\Communication\Console\Console;
use SprykerEco\Zed\Algolia\Business\Exception\AlgoliaEntityExporterNotFoundException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @method \SprykerEco\Zed\Algolia\Business\AlgoliaFacadeInterface getFacade()
 * @method \SprykerEco\Zed\Algolia\Communication\AlgoliaCommunicationFactory getFactory()
 * @method \SprykerEco\Zed\Algolia\Business\AlgoliaBusinessFactory getBusinessFactory()
 */
class AlgoliaEntityExportConsole extends Console
{
    /**
     * @var string
     */
    public const COMMAND_NAME = 'algolia:entity-export';

    /**
     * @var string
     */
    public const DESCRIPTION = 'Export entities to Algolia index';

    /**
     * @var string
     */
    protected const ARGUMENT_ENTITY_TYPE = 'entity-type';

    /**
     * @var string
     */
    protected const OPTION_ALL = 'all';

    /**
     * @var string
     */
    protected const OPTION_STORE = 'store';

    /**
     * @var string
     */
    protected const OPTION_LOCALE = 'locale';

    /**
     * @var string
     */
    protected const OPTION_CHUNK_SIZE = 'chunk-size';

    /**
     * @var string
     */
    protected const OPTION_DRY_RUN = 'dry-run';

    protected function configure(): void
    {
        $this->setName(static::COMMAND_NAME)
            ->setDescription(static::DESCRIPTION)
            ->addArgument(
                static::ARGUMENT_ENTITY_TYPE,
                InputArgument::OPTIONAL,
                'Entity type to export (e.g., product, cms-page). Use --all to export all types.',
            )
            ->addOption(
                static::OPTION_ALL,
                'a',
                InputOption::VALUE_NONE,
                'Export all registered entity types',
            )
            ->addOption(
                static::OPTION_STORE,
                's',
                InputOption::VALUE_OPTIONAL,
                'Filter entities by store reference (e.g., DE, US)',
            )
            ->addOption(
                static::OPTION_LOCALE,
                'l',
                InputOption::VALUE_OPTIONAL,
                'Filter entities by locale (e.g., de_DE, en_US)',
            )
            ->addOption(
                static::OPTION_CHUNK_SIZE,
                'c',
                InputOption::VALUE_OPTIONAL,
                'Number of entities to process per batch',
            )
            ->addOption(
                static::OPTION_DRY_RUN,
                'd',
                InputOption::VALUE_NONE,
                'Dry run mode - preview what would be exported without actually exporting',
            );

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption(static::OPTION_ALL)) {
            return $this->exportAllEntityTypes($input, $output);
        }

        /** @var string|null $entityType */
        $entityType = $input->getArgument(static::ARGUMENT_ENTITY_TYPE);

        if (!$entityType) {
            $this->showAvailableEntityTypes($output);

            return static::CODE_ERROR;
        }

        return $this->exportEntityType($entityType, $input, $output);
    }

    protected function exportAllEntityTypes(InputInterface $input, OutputInterface $output): int
    {
        $availableEntityTypes = $this->getBusinessFactory()->createAlgoliaEntityExporter()->getAvailableEntityTypes();

        if (!$availableEntityTypes) {
            $this->error('No entity exporters are registered. Please register entity exporter plugins in AlgoliaDependencyProvider.');

            return static::CODE_ERROR;
        }

        $output->writeln(sprintf('<info>Exporting %d entity types...</info>', count($availableEntityTypes)));
        $output->writeln('');

        $hasFailures = false;

        foreach ($availableEntityTypes as $entityType) {
            $result = $this->exportEntityType($entityType, $input, $output);

            if ($result !== static::CODE_SUCCESS) {
                $hasFailures = true;
            }

            $output->writeln('');
        }

        return $hasFailures ? static::CODE_ERROR : static::CODE_SUCCESS;
    }

    protected function exportEntityType(
        string $entityType,
        InputInterface $input,
        OutputInterface $output
    ): int {
        $criteriaTransfer = $this->buildCriteriaTransfer($entityType, $input);

        try {
            $this->showExportHeader($criteriaTransfer, $output);
            $resultTransfer = $this->getBusinessFactory()
                ->createAlgoliaEntityExporter()
                ->exportEntities($criteriaTransfer, $output);
            $this->showExportSummary($resultTransfer, $output);

            return $resultTransfer->getIsSuccessful() ? static::CODE_SUCCESS : static::CODE_ERROR;
        } catch (AlgoliaEntityExporterNotFoundException $exception) {
            $this->error($exception->getMessage());

            return static::CODE_ERROR;
        }
    }

    protected function buildCriteriaTransfer(string $entityType, InputInterface $input): AlgoliaExportCriteriaTransfer
    {
        $criteriaTransfer = new AlgoliaExportCriteriaTransfer();
        $criteriaTransfer->setEntityType($entityType);

        /** @var string|null $storeName */
        $storeName = $input->getOption(static::OPTION_STORE);
        if ($storeName) {
            $criteriaTransfer->setStoreName($storeName);
        }

        /** @var string|null $locale */
        $locale = $input->getOption(static::OPTION_LOCALE);
        if ($locale) {
            $criteriaTransfer->setLocale($locale);
        }

        /** @var string|null $chunkSize */
        $chunkSize = $input->getOption(static::OPTION_CHUNK_SIZE);
        if ($chunkSize !== null) {
            $criteriaTransfer->setChunkSize((int)$chunkSize);
        }

        $criteriaTransfer->setIsDryRun((bool)$input->getOption(static::OPTION_DRY_RUN));

        return $criteriaTransfer;
    }

    protected function showAvailableEntityTypes(OutputInterface $output): void
    {
        $availableEntityTypes = $this->getBusinessFactory()->createAlgoliaEntityExporter()->getAvailableEntityTypes();

        if (!$availableEntityTypes) {
            $this->error('No entity exporters are registered. Please register entity exporter plugins in AlgoliaDependencyProvider.');

            return;
        }

        $this->info('Available entity types:');
        foreach ($availableEntityTypes as $entityType) {
            $output->writeln(sprintf('  - <comment>%s</comment>', $entityType));
        }

        $output->writeln('');
        $this->info(sprintf('Usage: %s <entity-type> [options]', static::COMMAND_NAME));
        $this->info(sprintf('   or: %s --all [options]', static::COMMAND_NAME));
    }

    protected function showExportHeader(AlgoliaExportCriteriaTransfer $criteriaTransfer, OutputInterface $output): void
    {
        $output->writeln(sprintf(
            '<info>Exporting entity type:</info> <comment>%s</comment>%s',
            $criteriaTransfer->getEntityTypeOrFail(),
            $criteriaTransfer->getIsDryRun() ? ' <fg=yellow>(DRY RUN)</>' : '',
        ));

        if ($criteriaTransfer->getStoreName()) {
            $output->writeln(sprintf('  <info>Store:</info> %s', $criteriaTransfer->getStoreName()));
        }

        if ($criteriaTransfer->getLocale()) {
            $output->writeln(sprintf('  <info>Locale:</info> %s', $criteriaTransfer->getLocale()));
        }

        if ($criteriaTransfer->getChunkSize()) {
            $output->writeln(sprintf('  <info>Chunk size:</info> %d', $criteriaTransfer->getChunkSize()));
        }
    }

    protected function showExportSummary(AlgoliaExportResultTransfer $resultTransfer, OutputInterface $output): void
    {
        $output->writeln('');
        $output->writeln('<info>Export Summary:</info>');
        $output->writeln(sprintf('  Total entities: <comment>%d</comment>', $resultTransfer->getTotalCount() ?? 0));
        $output->writeln(sprintf('  Exported: <comment>%d</comment>', $resultTransfer->getExportedCount() ?? 0));

        if ($resultTransfer->getFailedCount()) {
            $output->writeln(sprintf('  <error>Failed: %d</error>', $resultTransfer->getFailedCount()));
        }

        if ($resultTransfer->getMessages()) {
            $output->writeln('');
            foreach ($resultTransfer->getMessages() as $message) {
                $output->writeln(sprintf('  %s', $message));
            }
        }

        $output->writeln('');

        if ($resultTransfer->getIsSuccessful()) {
            $this->success('Export completed successfully!');
        } else {
            $this->error('Export completed with errors.');
        }
    }
}
