<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Exporter;

use Generated\Shared\Transfer\AlgoliaExportCriteriaTransfer;
use Generated\Shared\Transfer\AlgoliaExportResultTransfer;
use SprykerEco\Zed\Algolia\AlgoliaConfig;
use SprykerEco\Zed\Algolia\Business\Exception\AlgoliaEntityExporterNotFoundException;
use Symfony\Component\Console\Output\OutputInterface;

class AlgoliaEntityExporter implements AlgoliaEntityExporterInterface
{
    /**
     * @var array<\SprykerEco\Zed\Algolia\Dependency\Plugin\AlgoliaEntityExporterPluginInterface>
     */
    protected array $entityExporterPlugins;

    /**
     * @var \SprykerEco\Zed\Algolia\AlgoliaConfig
     */
    protected AlgoliaConfig $config;

    /**
     * @var array<string, \SprykerEco\Zed\Algolia\Dependency\Plugin\AlgoliaEntityExporterPluginInterface>|null
     */
    protected ?array $entityExporterPluginMap = null;

    /**
     * @param array<\SprykerEco\Zed\Algolia\Dependency\Plugin\AlgoliaEntityExporterPluginInterface> $entityExporterPlugins
     */
    public function __construct(
        array $entityExporterPlugins,
        AlgoliaConfig $config
    ) {
        $this->entityExporterPlugins = $entityExporterPlugins;
        $this->config = $config;
    }

    /**
     * @throws \SprykerEco\Zed\Algolia\Business\Exception\AlgoliaEntityExporterNotFoundException
     */
    public function exportEntities(
        AlgoliaExportCriteriaTransfer $criteriaTransfer,
        OutputInterface $output
    ): AlgoliaExportResultTransfer {
        if (!$this->config->getIsActive()) {
            return (new AlgoliaExportResultTransfer())
                ->addMessage('Algolia module is not active. Please enable it in the configuration.')
                ->setIsSuccessful(false);
        }

        $criteriaTransfer->requireEntityType();

        $entityType = $criteriaTransfer->getEntityTypeOrFail();
        $plugin = $this->findEntityExporterPlugin($entityType);

        if (!$plugin) {
            throw new AlgoliaEntityExporterNotFoundException(
                sprintf(
                    'No Algolia entity exporter plugin found for entity type "%s". Available types: %s',
                    $entityType,
                    implode(', ', $this->getAvailableEntityTypes()),
                ),
            );
        }

        $criteriaTransfer = $this->ensureDefaultChunkSize($criteriaTransfer);

        return $plugin->export($criteriaTransfer, $output);
    }

    /**
     * @return array<string>
     */
    public function getAvailableEntityTypes(): array
    {
        $this->buildEntityExporterPluginMap();

        return array_keys($this->entityExporterPluginMap);
    }

    /**
     * @return \SprykerEco\Zed\Algolia\Dependency\Plugin\AlgoliaEntityExporterPluginInterface|null
     */
    protected function findEntityExporterPlugin(string $entityType): ?object
    {
        $this->buildEntityExporterPluginMap();

        return $this->entityExporterPluginMap[$entityType] ?? null;
    }

    protected function buildEntityExporterPluginMap(): void
    {
        if ($this->entityExporterPluginMap !== null) {
            return;
        }

        $this->entityExporterPluginMap = [];
        foreach ($this->entityExporterPlugins as $plugin) {
            $this->entityExporterPluginMap[$plugin->getEntityType()] = $plugin;
        }
    }

    protected function ensureDefaultChunkSize(AlgoliaExportCriteriaTransfer $criteriaTransfer): AlgoliaExportCriteriaTransfer
    {
        if ($criteriaTransfer->getChunkSize() === null) {
            $criteriaTransfer->setChunkSize($this->config->getDefaultExportChunkSize());
        }

        return $criteriaTransfer;
    }
}
