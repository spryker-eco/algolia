<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Algolia\Api\Client;

use Algolia\AlgoliaSearch\Api\SearchClient;
use Algolia\AlgoliaSearch\Exceptions\NotFoundException;
use Generated\Shared\Transfer\AlgoliaSearchParametersTransfer;
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

    public function search(string $query, AlgoliaSearchParametersTransfer $algoliaSearchParametersTransfer): AlgoliaSearchResponseTransfer
    {
        $searchParams = $algoliaSearchParametersTransfer->getSearchParams();
        $requestOptions = $algoliaSearchParametersTransfer->getRequestOptions();
        $requestData = ['query' => $query, 'searchParameters' => $searchParams];

        try {
            $result = $this->searchClient->searchSingleIndex($this->indexName, ['query' => $query] + $searchParams, $requestOptions);
            $responseData = $result;

            return (new AlgoliaSearchResponseTransfer())
                ->setSearchResults($result)
                ->setIsSuccessful(true);
        } catch (NotFoundException $notFoundException) {
            $responseData = ['error' => $notFoundException->getMessage()];

            $this->getLogger()->warning('Algolia replica index not found for sort, returning empty result.', [
                'indexName' => $this->indexName,
                'exception' => $notFoundException,
            ]);

            return (new AlgoliaSearchResponseTransfer())
                ->setIsSuccessful(true);
        } catch (Throwable $e) {
            $responseData = ['error' => $e->getMessage()];

            $this->getLogger()->warning('Algolia search request failed.', [
                'indexName' => $this->indexName,
                'exception' => $e,
            ]);

            return (new AlgoliaSearchResponseTransfer())
                ->setIsSuccessful(false);
        } finally {
            $this->logHttpRequest('POST', 'search', $requestData, $responseData);
        }
    }

    public function getSettings(): array
    {
        return $this->searchClient->getSettings($this->indexName);
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
