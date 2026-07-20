<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Handler;

use Algolia\AlgoliaSearch\Api\QuerySuggestionsClient;
use Algolia\AlgoliaSearch\Api\SearchClient;
use Algolia\AlgoliaSearch\Exceptions\NotFoundException;
use SprykerEco\Zed\Algolia\AlgoliaConfig;
use Throwable;

class SuggestionIndexHandler implements SuggestionIndexHandlerInterface
{
    protected const string REGION_US = 'us';

    protected const string REGION_EU = 'eu';

    protected const string QUERY_SUGGESTIONS_SUFFIX = 'query_suggestions';

    public function __construct(protected AlgoliaConfig $algoliaConfig)
    {
    }

    public function createProductSuggestionsIndex(string $sourceIndex, SearchClient $searchClient): void
    {
        $suggestionIndexName = sprintf('%s_%s', $sourceIndex, static::QUERY_SUGGESTIONS_SUFFIX);

        $isConfigurationExist = $this->executeWithRegionFallback(
            $searchClient,
            fn (QuerySuggestionsClient $querySuggestionsClient): bool => $this->checkSuggestionIndexConfigurationExist($suggestionIndexName, $querySuggestionsClient),
        );

        if ($isConfigurationExist) {
            return;
        }

        $generateAttributes = $this->algoliaConfig->getSuggestionGenerateAttributes();

        if (!$generateAttributes) {
            return;
        }

        $suggestionsIndexOptions = [
            'indexName' => $suggestionIndexName,
            'sourceIndices' => [
                [
                    'indexName' => $sourceIndex,
                    'minHits' => 1,
                    'minLetters' => 2,
                    'generate' => $generateAttributes,
                ],
            ],
            'allowSpecialCharacters' => true,
        ];

        $this->executeWithRegionFallback(
            $searchClient,
            function (QuerySuggestionsClient $querySuggestionsClient) use ($suggestionsIndexOptions): void {
                $querySuggestionsClient->createConfig($suggestionsIndexOptions);
            },
        );
    }

    protected function checkSuggestionIndexConfigurationExist(string $configurationName, QuerySuggestionsClient $querySuggestionsClient): bool
    {
        try {
            $querySuggestionsClient->getConfig($configurationName);
        } catch (NotFoundException $exception) {
            return false;
        }

        return true;
    }

    /**
     * Query Suggestions is only available in the `us`/`eu` regions and the v4 SDK does not expose a
     * reliable, typed way to detect a region mismatch up front (it no longer throws the message-based
     * exceptions the previous SDK version did), so the correct region is resolved by attempting the
     * real operation against `REGION_US` first and retrying once against `REGION_EU` on any failure.
     *
     * @template T
     *
     * @param callable(\Algolia\AlgoliaSearch\Api\QuerySuggestionsClient): T $operation
     *
     * @return T
     */
    protected function executeWithRegionFallback(SearchClient $searchClient, callable $operation)
    {
        try {
            return $operation($this->createQuerySuggestionsClientForRegion($searchClient, static::REGION_US));
        } catch (Throwable $exception) {
            return $operation($this->createQuerySuggestionsClientForRegion($searchClient, static::REGION_EU));
        }
    }

    protected function createQuerySuggestionsClientForRegion(SearchClient $searchClient, string $region): QuerySuggestionsClient
    {
        $config = $searchClient->getClientConfig();

        return QuerySuggestionsClient::create($config->getAppId(), $config->getAlgoliaApiKey(), $region);
    }
}
