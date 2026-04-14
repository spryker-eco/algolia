<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Algolia\Business\Saver;

use Algolia\AlgoliaSearch\SearchClient;
use Exception;
use Generated\Shared\Transfer\AlgoliaConfigTransfer;
use Generated\Shared\Transfer\AlgoliaResponseTransfer;
use Generated\Shared\Transfer\IndexConfigurationTransfer;
use SprykerEco\Shared\Algolia\Enum\AlgoliaCmsPageObjectEnum;
use SprykerEco\Zed\Algolia\AlgoliaConfig;
use SprykerEco\Zed\Algolia\Business\Api\Client\SearchIndexClientInterface;
use SprykerEco\Zed\Algolia\Business\Api\Creator\SearchClientCreatorInterface;
use SprykerEco\Zed\Algolia\Business\Api\Creator\SearchIndexClientCreatorInterface;
use SprykerEco\Zed\Algolia\Business\Api\IndexConfigurator\IndexConfiguratorInterface;

class CmsPageSaver implements CmsPageSaverInterface
{
    public function __construct(
        protected readonly SearchClientCreatorInterface $searchClientCreator,
        protected readonly SearchIndexClientCreatorInterface $searchIndexClientCreator,
        protected readonly IndexConfiguratorInterface $indexConfigurator,
        protected readonly AlgoliaConfig $config
    ) {
    }

    /**
     * @param array $indexData
     */
    public function saveCmsPage(array $indexData, AlgoliaConfigTransfer $algoliaConfigTransfer): AlgoliaResponseTransfer
    {
        if (!$indexData) {
            return $this->createSuccessResponse();
        }

        $searchClient = $this->searchClientCreator->createSearchClientFromConfig($algoliaConfigTransfer);
        $indexName = $indexData['indexName'];
        $locale = $indexData['locale'] ?? null;

        $searchIndexClient = $this->searchIndexClientCreator->createSearchIndexApiClient(
            $searchClient,
            (new IndexConfigurationTransfer())
                ->setAlgoliaConfig($algoliaConfigTransfer)
                ->setIndexName($indexName)
                ->setLocale($locale)
                ->setSkipIndexConfiguration(true),
        );

        // Configure index settings and replicas
         $this->configureIndexSettings($searchClient, $searchIndexClient, $indexName);

        $algoliaDataArray = $indexData['data'] ?? null;
        if ($algoliaDataArray === null) {
            return $this->createSuccessResponse();
        }

        $searchIndexClient->saveObjects([$algoliaDataArray]);

        return $this->createSuccessResponse();
    }

    /**
     * Configures the Algolia index settings for CMS pages.
     */
    protected function configureIndexSettings(
        SearchClient $searchClient,
        SearchIndexClientInterface $searchIndexClient,
        string $indexName
    ): void {
        $replicaNames = $this->getCmsPageReplicaNames($indexName);

        // Base settings for main index and replicas
        $baseSettings = [
            'searchableAttributes' => $this->config->getCmsPageSearchableAttributes(),
            'customRanking' => $this->config->getCmsPageCustomRanking(),
            'attributesForFaceting' => $this->getMergedAttributesForFaceting($searchIndexClient),
        ];

        // configure main index with replicas names
        $mainIndexSettings = $baseSettings + [
                'replicas' => array_keys($replicaNames),
            ];

        $searchIndexClient->setSettings($mainIndexSettings);
        $this->configureReplicas($searchClient, $replicaNames, $baseSettings);
    }

    /**
     * Configure replicas with base settings plus specific ranking
     *
     * @param array $replicaNames
     * @param array $baseSettings
     */
    protected function configureReplicas(SearchClient $searchClient, array $replicaNames, array $baseSettings): void
    {
        foreach ($replicaNames as $replicaName => $rankingAttributes) {
            $replicaIndex = $searchClient->initIndex($replicaName);

            $replicaSettings = $baseSettings + [
                    'ranking' => $rankingAttributes,
                ];

            $replicaIndex->setSettings($replicaSettings);
        }
    }

    /**
     * Merges existing attributesForFaceting from Algolia with required CMS page faceting attributes.
     * This preserves custom facets created in Algolia dashboard while ensuring required facets are present.
     *
     * @return array<string>
     */
    protected function getMergedAttributesForFaceting(SearchIndexClientInterface $searchIndexClient): array
    {
        $requiredAttributes = [
            AlgoliaCmsPageObjectEnum::STORE->value,
            AlgoliaCmsPageObjectEnum::VALID_FROM->value,
            AlgoliaCmsPageObjectEnum::VALID_TO->value,
        ];

        try {
            // Get current settings from Algolia to preserve custom facets
            $currentSettings = $searchIndexClient->getSettings();
            $existingAttributes = $currentSettings['attributesForFaceting'] ?? [];
        } catch (Exception $exception) {
            // If we can't get current settings (e.g., index doesn't exist yet), use only required attributes
            $existingAttributes = [];
        }

        $mergedAttributes = array_unique(array_merge($existingAttributes, $requiredAttributes));

        return array_values($mergedAttributes);
    }

    /**
     * @return array
     */
    protected function getCmsPageReplicaNames(string $indexName): array
    {
        $cmsPageReplicaNames = [];
        $cmsPageSortingAttributes = $this->config->getCmsPageSortingAttributes();

        foreach ($cmsPageSortingAttributes as $cmsPageSortingAttribute) {
            $cmsPageReplicaNames = array_merge($cmsPageReplicaNames, $this->indexConfigurator->getReplicaNameWithRankingAttributes($indexName, $cmsPageSortingAttribute));
        }

        return $cmsPageReplicaNames;
    }

    protected function createSuccessResponse(): AlgoliaResponseTransfer
    {
        return (new AlgoliaResponseTransfer())->setIsSuccessful(true);
    }
}
