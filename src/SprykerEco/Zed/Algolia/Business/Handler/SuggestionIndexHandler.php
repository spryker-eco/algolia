<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Handler;

use Algolia\AlgoliaSearch\Api\QuerySuggestionsClient;
use Algolia\AlgoliaSearch\Api\SearchClient;
use Algolia\AlgoliaSearch\Exceptions\NotFoundException;
use SprykerEco\Shared\Algolia\Enum\AlgoliaProductObjectEnum;
use Throwable;

class SuggestionIndexHandler implements SuggestionIndexHandlerInterface
{
    protected const string REGION_US = 'us';

    protected const string REGION_EU = 'eu';

    protected const string QUERY_SUGGESTIONS_SUFFIX = 'query_suggestions';

    public function createProductSuggestionsIndex(string $sourceIndex, SearchClient $searchClient): void
    {
        $suggestionIndexName = sprintf('%s_%s', $sourceIndex, static::QUERY_SUGGESTIONS_SUFFIX);
        $querySuggestionsClient = $this->createQuerySuggestionsClient($searchClient);

        $isConfigurationExist = $this->checkSuggestionIndexConfigurationExist($suggestionIndexName, $querySuggestionsClient);

        if ($isConfigurationExist) {
            return;
        }

        $suggestionsIndexOptions = [
            'indexName' => $suggestionIndexName,
            'sourceIndices' => [
                [
                    'indexName' => $sourceIndex,
                    'minHits' => 1,
                    'minLetters' => 2,
                    'generate' => [
                        [AlgoliaProductObjectEnum::CATEGORY->value],
                        [AlgoliaProductObjectEnum::ATTRIBUTES->value . '.brand'],
                    ],
                ],
            ],
            'allowSpecialCharacters' => true,
        ];

        $querySuggestionsClient->createConfig($suggestionsIndexOptions);
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

    public function getAllConfigurations(SearchClient $searchClient): array
    {
        $querySuggestionsClient = $this->createQuerySuggestionsClient($searchClient);

        return $querySuggestionsClient->getAllConfigs();
    }

    protected function createQuerySuggestionsClient(SearchClient $searchClient): QuerySuggestionsClient
    {
        $config = $searchClient->getClientConfig();
        $appId = $config->getAppId();
        $apiKey = $config->getAlgoliaApiKey();

        try {
            $client = QuerySuggestionsClient::create($appId, $apiKey, static::REGION_US);
            // Verify the client works by making a lightweight call
            $client->getAllConfigs();

            return $client;
        } catch (Throwable $exception) {
            if ($this->isRegionMismatchException($exception)) {
                return QuerySuggestionsClient::create($appId, $apiKey, static::REGION_EU);
            }

            throw $exception;
        }
    }

    protected function isRegionMismatchException(Throwable $exception): bool
    {
        $message = $exception->getMessage();

        if ($message === 'The log processing region does not match') {
            return true;
        }

        // Algolia may return a redirect or JSON parse error for incorrect region
        if (mb_stristr($message, 'json_decode_error') !== false) {
            return true;
        }

        return false;
    }
}
