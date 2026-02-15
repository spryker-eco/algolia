<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Algolia\Api\Client;

use Algolia\AlgoliaSearch\SearchIndex;
use Generated\Shared\Transfer\AlgoliaResponseTransfer;
use Generated\Shared\Transfer\AlgoliaSearchResponseTransfer;
use Spryker\Shared\Http\Logger\ExternalHttpInMemoryLoggerTrait;
use Spryker\Shared\Log\LoggerTrait;
use Throwable;

class SearchIndexClient implements SearchIndexClientInterface
{
    use LoggerTrait;
    use ExternalHttpInMemoryLoggerTrait;

    public function __construct(protected SearchIndex $searchIndex)
    {
    }

    /**
     * @param array<array<string, mixed>> $algoliaObjectTransfers
     */
    public function saveObjects(array $algoliaObjectTransfers): AlgoliaResponseTransfer
    {
        $this->searchIndex->saveObjects($algoliaObjectTransfers);

        return $this->createSuccessfulAlgoliaResponseTransfer();
    }

    /**
     * @param array<string> $objectIds
     */
    public function deleteObjects(array $objectIds): AlgoliaResponseTransfer
    {
        $this->searchIndex->deleteObjects($objectIds);

        return $this->createSuccessfulAlgoliaResponseTransfer();
    }

    /**
     * @param array<string, mixed> $searchParameters
     */
    public function search(string $query, array $searchParameters): AlgoliaSearchResponseTransfer
    {
        $requestData = ['query' => $query, 'searchParameters' => $searchParameters];

        try {
            $result = $this->searchIndex->search($query, $searchParameters);
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
        return $this->searchIndex->exists();
    }

    public function getIndexName(): string
    {
        return $this->searchIndex->getIndexName();
    }

    public function getSettings(): array
    {
        return $this->searchIndex->getSettings();
    }

    /**
     * @param array<string, mixed> $settings
     */
    public function setSettings(array $settings): AlgoliaResponseTransfer
    {
        $this->searchIndex->setSettings($settings);

        return $this->createSuccessfulAlgoliaResponseTransfer();
    }

    public function getSearchIndex(): SearchIndex
    {
        return $this->searchIndex;
    }

    protected function createSuccessfulAlgoliaResponseTransfer(): AlgoliaResponseTransfer
    {
        return (new AlgoliaResponseTransfer())
            ->setIsSuccessful(true);
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
        $url = sprintf('algolia://%s/%s', $this->searchIndex->getIndexName(), $endpoint);

        $this->getExternalHttpInMemoryLogger()->log(
            $method,
            $url,
            $requestData,
            $responseData,
        );
    }
}
