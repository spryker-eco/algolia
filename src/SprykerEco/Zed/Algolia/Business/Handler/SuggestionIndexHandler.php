<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Algolia\Business\Handler;

use Algolia\AlgoliaSearch\Exceptions\BadRequestException;
use Algolia\AlgoliaSearch\Exceptions\NotFoundException;
use Algolia\AlgoliaSearch\SearchClient;
use InvalidArgumentException;
use SprykerEco\Zed\Algolia\AlgoliaConfig;
use Throwable;

class SuggestionIndexHandler implements SuggestionIndexHandlerInterface
{
    /**
     * @var string
     */
    protected const QUERY_SUGGESTION_BASE_URL_US = 'query-suggestions.us.algolia.com';

    /**
     * @var string
     */
    protected const QUERY_SUGGESTION_BASE_URL_EU = 'query-suggestions.eu.algolia.com';

    public function __construct(protected AlgoliaConfig $algoliaConfig)
    {
    }

    public function createSuggestionsIndex(string $sourceIndex, SearchClient $searchClient): void
    {
        $suggestionIndexName = sprintf('%s_%s', $sourceIndex, $this->algoliaConfig->getQuerySuggestionsSuffix());
        # suggestion indexes are not (at the time of writing) deleted when disconnecting, will be implemented in PBC-2843
        $isConfigurationExist = $this->checkSuggestionIndexConfigurationExist($suggestionIndexName, $searchClient);

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
                        [AlgoliaConfig::ATTRIBUTE_NAME_CATEGORY],
                        [AlgoliaConfig::ATTRIBUTE_NAME_BRAND],
                    ],
                ],
            ],
            'allowSpecialCharacters' => true,
        ];

        $this->executeSearchClientCall($searchClient, 'POST', '/1/configs', $suggestionsIndexOptions);
    }

    /**
     * @throws \Algolia\AlgoliaSearch\Exceptions\BadRequestException
     */
    protected function checkSuggestionIndexConfigurationExist(string $configurationName, SearchClient $searchClient): bool
    {
        try {
            $this->executeSearchClientCall($searchClient, 'GET', '/1/configs/' . $configurationName);
        } catch (BadRequestException $exception) {
            if ($exception instanceof NotFoundException) {
                return false;
            }

            throw $exception;
        }

        return true;
    }

    /**
     * @return array
     */
    public function getAllConfigurations(SearchClient $searchClient): array
    {
        return $this->executeSearchClientCall($searchClient, 'GET', '/1/configs');
    }

    public function deleteConfiguration(string $configurationName, SearchClient $searchClient): void
    {
        $this->executeSearchClientCall($searchClient, 'DELETE', '/1/configs/' . $configurationName);
    }

    /**
     * @param array $options
     *
     * @throws \Algolia\AlgoliaSearch\Exceptions\BadRequestException
     *
     * @return mixed
     */
    protected function executeSearchClientCall(SearchClient $searchClient, string $method, string $url, array $options = [])
    {
        try {
            return $searchClient->custom($method, $url, $options, [static::QUERY_SUGGESTION_BASE_URL_US]);
        } catch (BadRequestException $exception) {
            if ($exception->getMessage() === 'The log processing region does not match') {
                return $searchClient->custom($method, $url, $options, [static::QUERY_SUGGESTION_BASE_URL_EU]);
            } else {
                throw $exception;
            }
        } catch (InvalidArgumentException $exception) {
            if ($this->isExceptionRelatedToIncorrectProcessingRegion($exception)) {
                return $searchClient->custom($method, $url, $options, [static::QUERY_SUGGESTION_BASE_URL_EU]);
            } else {
                throw $exception;
            }
        }
    }

    /**
     * @see \Algolia\AlgoliaSearch\Support\Helpers::json_decode()
     *
     * Right now Algolia respond as:
     * - GET requests: `<a href="https://query-suggestions.eu.algolia.com/1/configs">Temporary Redirect</a>.`
     * - DELETE/POST requests: empty string
     */
    protected function isExceptionRelatedToIncorrectProcessingRegion(Throwable $exception): bool
    {
        return mb_stristr($exception->getMessage(), 'json_decode_error') !== false;
    }
}
