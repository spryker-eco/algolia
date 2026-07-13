<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Algolia\Api\Client;

use Algolia\AlgoliaSearch\Api\SearchClient;
use Generated\Shared\Transfer\AlgoliaResponseTransfer;
use Generated\Shared\Transfer\AlgoliaSearchResponseTransfer;
use Spryker\Shared\Http\Logger\ExternalHttpInMemoryLoggerTrait;
use Spryker\Shared\Log\LoggerTrait;
use Throwable;

class SearchIndexClient implements SearchIndexClientInterface
{
    use LoggerTrait;
    use ExternalHttpInMemoryLoggerTrait;

    public function __construct(
        protected SearchClient $searchClient,
        protected string $indexName,
    ) {
    }

    /**
     * @param array<array<string, mixed>> $algoliaObjectTransfers
     */
    public function saveObjects(array $algoliaObjectTransfers): AlgoliaResponseTransfer
    {
        $this->searchClient->saveObjects($this->indexName, $algoliaObjectTransfers);

        return $this->createSuccessfulAlgoliaResponseTransfer();
    }

    /**
     * @param array<string> $objectIds
     */
    public function deleteObjects(array $objectIds): AlgoliaResponseTransfer
    {
        $this->searchClient->deleteObjects($this->indexName, $objectIds);

        return $this->createSuccessfulAlgoliaResponseTransfer();
    }

    /**
     * @param array<string, mixed> $searchParameters
     */
    public function search(string $query, array $searchParameters): AlgoliaSearchResponseTransfer
    {
        $requestData = ['query' => $query, 'searchParameters' => $searchParameters];
        $requestOptions = $this->extractRequestOptions($searchParameters);

        try {
            $result = $this->searchClient->searchSingleIndex($this->indexName, ['query' => $query] + $searchParameters, $requestOptions);
            $responseData = $result;

            return (new AlgoliaSearchResponseTransfer())
                ->setSearchResults($result)
                ->setIsSuccessful(true);
        } catch (Throwable $e) {
            $responseData = ['error' => $e->getMessage()];

            return (new AlgoliaSearchResponseTransfer())
                ->setIsSuccessful(false);
        } finally {
            $this->logHttpRequest('POST', 'search', $requestData, $responseData);
        }
    }

    public function indexExists(): bool
    {
        return $this->searchClient->indexExists($this->indexName);
    }

    public function getIndexName(): string
    {
        return $this->indexName;
    }

    public function getSettings(): array
    {
        return $this->searchClient->getSettings($this->indexName);
    }

    /**
     * @param array<string, mixed> $settings
     */
    public function setSettings(array $settings): AlgoliaResponseTransfer
    {
        $this->searchClient->setSettings($this->indexName, $settings);

        return $this->createSuccessfulAlgoliaResponseTransfer();
    }

    protected function createSuccessfulAlgoliaResponseTransfer(): AlgoliaResponseTransfer
    {
        return (new AlgoliaResponseTransfer())
            ->setIsSuccessful(true);
    }

    /**
     * Extracts non-search parameters (headers) from $searchParameters by reference,
     * removing them from the body so v4 strict validation does not reject them.
     *
     * @param array<string, mixed> $searchParameters Modified in place — extracted keys are unset.
     *
     * @return array<string, mixed>
     */
    protected function extractRequestOptions(array &$searchParameters): array
    {
        $requestOptions = [];

        if (isset($searchParameters['X-Forwarded-For'])) {
            $requestOptions['headers']['X-Forwarded-For'] = $searchParameters['X-Forwarded-For'];
            unset($searchParameters['X-Forwarded-For']);
        }

        return $requestOptions;
    }

    /**
     * @param array<string, mixed> $requestData
     * @param array<string, mixed>|null $responseData
     */
    protected function logHttpRequest(
        string $method,
        string $endpoint,
        array $requestData,
        ?array $responseData,
    ): void {
        $url = sprintf('algolia://%s/%s', $this->indexName, $endpoint);

        $this->getExternalHttpInMemoryLogger()->log(
            $method,
            $url,
            $requestData,
            $responseData,
        );
    }
}
