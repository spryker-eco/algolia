# Custom Entity Index Mapping Guide

## Table of Contents

- [Overview](#overview)
- [Prerequisites](#prerequisites)
- [Architecture](#architecture)
- [Step-by-Step Implementation](#step-by-step-implementation)
- [Real-World Examples](#real-world-examples)
- [Advanced Configuration](#advanced-configuration)
- [Troubleshooting](#troubleshooting)
- [Best Practices](#best-practices)

---

## Overview

The Custom Entity Index Mapping feature allows you to search any entity that is already indexed in Algolia without creating custom publisher plugins or extensive module customizations. This is ideal for:

- **Read-only search scenarios**: When you manage data indexing externally but want to search from Spryker
- **Third-party integrations**: When external systems index data into Algolia
- **Custom entities**: Documents, manufacturers, locations, events, or any custom business entity
- **Rapid prototyping**: Quick search implementation without full publisher integration

### What It Does

- Maps Spryker search source identifiers to existing Algolia indices
- Enables search queries to be routed to the correct Algolia index
- Supports multi-store and multi-locale configurations
- Works seamlessly with Spryker's search infrastructure

### What It Doesn't Do

- **Does not index data**: You need to populate Algolia indices separately
- **Does not publish events**: No real-time synchronization from Spryker
- **Does not manage index lifecycle**: Index creation/deletion handled externally

---

## Prerequisites

### Required

1. **Algolia Account**: Active Algolia account with indices already created
2. **Algolia Module Installed**: `spryker-eco/algolia` package installed and configured
3. **Existing Algolia Indices**: Your custom entity data already indexed in Algolia
4. **Basic Knowledge**: Understanding of Spryker's search architecture and query plugins

### Recommended

- Understanding of how Algolia indices are structured
- Knowledge of your entity data schema in Algolia
- Familiarity with Spryker's SearchContext and Query plugins

---

## Architecture

### How It Works

```
Storefront Search Request
        ↓
Search Query Plugin (with sourceIdentifier)
        ↓
AlgoliaSearchAdapterPlugin
        ↓
QueryApplicabilityChecker
        ↓ (checks entity-to-index mapping)
Entity-to-Index Mapping Config
        ↓
IndexNameResolver
        ↓ (resolves correct index name)
Algolia Search API
        ↓
Search Results
```

### Key Components

1. **Source Identifier**: A unique string identifying your entity type (e.g., "document", "manufacturer")
2. **Index Name**: The actual Algolia index name (e.g., "prod-documents-de_de")
3. **Mapping Configuration**: Array defining source identifier → index name relationships
4. **Query Plugin**: Spryker plugin that specifies the source identifier in SearchContext

---

## Step-by-Step Implementation

### Step 1: Prepare Algolia Index

First, ensure your custom entity data is indexed in Algolia. You can index data:

**Option A: Via Algolia Dashboard**
1. Go to Algolia dashboard
2. Create a new index (e.g., "documents_de")
3. Upload your data via JSON import

**Option B: Via Algolia API**
```php
// External script or service
$client = \Algolia\AlgoliaSearch\SearchClient::create('APP_ID', 'ADMIN_KEY');
$index = $client->initIndex('documents_de');

$documents = [
    [
        'objectID' => 'doc-1',
        'title' => 'Technical Documentation',
        'category' => 'Engineering',
        'content' => 'Detailed technical guide...',
        'url' => '/documents/technical-guide',
    ],
    // ... more documents
];

$index->saveObjects($documents);
```

**Option C: Via External System**
- Use your CMS, DAM, or other system to index data
- Ensure data includes all searchable fields

### Step 2: Configure Entity-to-Index Mapping

Create or extend `AlgoliaConfig` in your project:

```php
<?php

namespace Pyz\Shared\Algolia;

use SprykerEco\Shared\Algolia\AlgoliaConfig as SprykerEcoAlgoliaConfig;

class AlgoliaConfig extends SprykerEcoAlgoliaConfig
{
    /**
     * @return array<array<string, mixed>>
     */
    public function getEntityToIndexMappings(): array
    {
        return [
            // Single store, single locale
            [
                'sourceIdentifier' => 'document',
                'store' => 'DE',
                'locales' => ['de_DE'],
                'indexName' => 'documents_de',
            ],

            // Multi-locale support
            [
                'sourceIdentifier' => 'document',
                'store' => 'DE',
                'locales' => ['en_US'],
                'indexName' => 'documents_en',
            ],

            // Global entity (all stores, all locales)
            [
                'sourceIdentifier' => 'manufacturer',
                'store' => '*',
                'locales' => ['*'],
                'indexName' => 'manufacturers_global',
            ],

            // Multi-store with wildcard locale
            [
                'sourceIdentifier' => 'location',
                'store' => 'US',
                'locales' => ['*'],
                'indexName' => 'locations_us',
            ],
        ];
    }
}
```

### Step 3: Create a Search Query Plugin

Create a query plugin for your custom entity:

```php
<?php

namespace Pyz\Client\DocumentSearch\Plugin\Search;

use Generated\Shared\Transfer\SearchContextTransfer;
use Spryker\Client\Kernel\AbstractPlugin;
use Spryker\Client\SearchExtension\Dependency\Plugin\QueryInterface;
use Spryker\Client\SearchExtension\Dependency\Plugin\SearchContextAwareQueryInterface;

/**
 * @method \Pyz\Client\DocumentSearch\DocumentSearchFactory getFactory()
 */
class DocumentSearchQueryPlugin extends AbstractPlugin implements QueryInterface, SearchContextAwareQueryInterface
{
    /**
     * Source identifier must match the 'sourceIdentifier' in your mapping config
     */
    protected const SOURCE_IDENTIFIER = 'document';

    protected ?SearchContextTransfer $searchContextTransfer = null;

    /**
     * @return mixed
     */
    public function getSearchQuery()
    {
        // This can be empty or contain basic query structure
        // The actual query will be built by Algolia adapter
        return [];
    }

    /**
     * @return \Generated\Shared\Transfer\SearchContextTransfer
     */
    public function getSearchContext(): SearchContextTransfer
    {
        if ($this->searchContextTransfer === null) {
            $this->searchContextTransfer = (new SearchContextTransfer())
                ->setSourceIdentifier(static::SOURCE_IDENTIFIER);
        }

        return $this->searchContextTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\SearchContextTransfer $searchContextTransfer
     *
     * @return void
     */
    public function setSearchContext(SearchContextTransfer $searchContextTransfer): void
    {
        $this->searchContextTransfer = $searchContextTransfer;
    }
}
```

### Step 4: Create Client Module (if needed)

If you don't have a client module for your entity, create one:

**File: `src/Pyz/Client/DocumentSearch/DocumentSearchClient.php`**

```php
<?php

namespace Pyz\Client\DocumentSearch;

use Spryker\Client\Kernel\AbstractClient;

/**
 * @method \Pyz\Client\DocumentSearch\DocumentSearchFactory getFactory()
 */
class DocumentSearchClient extends AbstractClient implements DocumentSearchClientInterface
{
    /**
     * @param array $requestParameters
     *
     * @return array
     */
    public function search(array $requestParameters): array
    {
        return $this->getFactory()
            ->getSearchClient()
            ->search($this->getFactory()->createDocumentSearchQuery(), $requestParameters);
    }
}
```

**File: `src/Pyz/Client/DocumentSearch/DocumentSearchFactory.php`**

```php
<?php

namespace Pyz\Client\DocumentSearch;

use Pyz\Client\DocumentSearch\Plugin\Search\DocumentSearchQueryPlugin;
use Spryker\Client\Kernel\AbstractFactory;
use Spryker\Client\Search\SearchClientInterface;

/**
 * @method \Pyz\Client\DocumentSearch\DocumentSearchConfig getConfig()
 */
class DocumentSearchFactory extends AbstractFactory
{
    /**
     * @return \Spryker\Client\SearchExtension\Dependency\Plugin\QueryInterface
     */
    public function createDocumentSearchQuery()
    {
        return new DocumentSearchQueryPlugin();
    }

    /**
     * @return \Spryker\Client\Search\SearchClientInterface
     */
    public function getSearchClient(): SearchClientInterface
    {
        return $this->getProvidedDependency(DocumentSearchDependencyProvider::CLIENT_SEARCH);
    }
}
```

**File: `src/Pyz/Client/DocumentSearch/DocumentSearchDependencyProvider.php`**

```php
<?php

namespace Pyz\Client\DocumentSearch;

use Spryker\Client\Kernel\AbstractDependencyProvider;
use Spryker\Client\Kernel\Container;

class DocumentSearchDependencyProvider extends AbstractDependencyProvider
{
    public const CLIENT_SEARCH = 'CLIENT_SEARCH';

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    public function provideServiceLayerDependencies(Container $container): Container
    {
        $container = parent::provideServiceLayerDependencies($container);
        $container = $this->addSearchClient($container);

        return $container;
    }

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    protected function addSearchClient(Container $container): Container
    {
        $container->set(static::CLIENT_SEARCH, function (Container $container) {
            return $container->getLocator()->search()->client();
        });

        return $container;
    }
}
```

### Step 5: Use in Your Application

**In a Controller:**

```php
<?php

namespace Pyz\Yves\DocumentSearch\Controller;

use Spryker\Yves\Kernel\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \Pyz\Client\DocumentSearch\DocumentSearchClientInterface getClient()
 */
class SearchController extends AbstractController
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Spryker\Yves\Kernel\View\View
     */
    public function indexAction(Request $request)
    {
        $searchTerm = $request->query->get('q', '');

        $searchResults = $this->getClient()->search([
            'q' => $searchTerm,
            'page' => $request->query->getInt('page', 1),
            'ipp' => 12,
        ]);

        return $this->view([
            'searchTerm' => $searchTerm,
            'results' => $searchResults,
        ], [], '@DocumentSearch/views/search/index.twig');
    }
}
```

---

## Real-World Examples

### Example 1: Multi-Store Document Library

**Scenario**: A company has technical documentation indexed per store with different languages.

**Algolia Indices**:
- `docs-de-de_de` (German docs for DE store)
- `docs-de-en_us` (English docs for DE store)
- `docs-us-en_us` (English docs for US store)

**Configuration**:

```php
public function getEntityToIndexMappings(): array
{
    return [
        [
            'sourceIdentifier' => 'document',
            'store' => 'DE',
            'locales' => ['de_DE'],
            'indexName' => 'docs-de-de_de',
        ],
        [
            'sourceIdentifier' => 'document',
            'store' => 'DE',
            'locales' => ['en_US'],
            'indexName' => 'docs-de-en_us',
        ],
        [
            'sourceIdentifier' => 'document',
            'store' => 'US',
            'locales' => ['en_US'],
            'indexName' => 'docs-us-en_us',
        ],
    ];
}
```

**How It Works**:
- When a user on DE store searches in German, queries go to `docs-de-de_de`
- When a user on DE store searches in English, queries go to `docs-de-en_us`
- When a user on US store searches, queries go to `docs-us-en_us`

### Example 2: Global Manufacturer Directory

**Scenario**: A B2B platform has manufacturer data that's the same across all stores and languages.

**Algolia Index**:
- `manufacturers` (single global index)

**Configuration**:

```php
public function getEntityToIndexMappings(): array
{
    return [
        [
            'sourceIdentifier' => 'manufacturer',
            'store' => '*',
            'locales' => ['*'],
            'indexName' => 'manufacturers',
        ],
    ];
}
```

**Data Structure in Algolia**:
```json
{
    "objectID": "mfg-001",
    "name": "ACME Corporation",
    "country": "USA",
    "industry": "Manufacturing",
    "certifications": ["ISO9001", "ISO14001"],
    "website": "https://acme.com",
    "logo_url": "https://cdn.acme.com/logo.png"
}
```

### Example 3: Store-Specific Locations

**Scenario**: Retail chain with different locations per country, any language.

**Algolia Indices**:
- `locations-de` (German locations)
- `locations-us` (US locations)

**Configuration**:

```php
public function getEntityToIndexMappings(): array
{
    return [
        [
            'sourceIdentifier' => 'store-location',
            'store' => 'DE',
            'locales' => ['*'], // Works for all languages
            'indexName' => 'locations-de',
        ],
        [
            'sourceIdentifier' => 'store-location',
            'store' => 'US',
            'locales' => ['*'],
            'indexName' => 'locations-us',
        ],
    ];
}
```

**Query Plugin**:

```php
class StoreLocationSearchQueryPlugin extends AbstractPlugin implements QueryInterface, SearchContextAwareQueryInterface
{
    protected const SOURCE_IDENTIFIER = 'store-location';

    // ... implementation similar to DocumentSearchQueryPlugin
}
```

### Example 4: Events Calendar

**Scenario**: Event management with localized event data.

**Algolia Indices**:
- `events-de_de`
- `events-en_us`

**Configuration**:

```php
public function getEntityToIndexMappings(): array
{
    return [
        [
            'sourceIdentifier' => 'event',
            'store' => '*', // Available in all stores
            'locales' => ['de_DE'],
            'indexName' => 'events-de_de',
        ],
        [
            'sourceIdentifier' => 'event',
            'store' => '*',
            'locales' => ['en_US'],
            'indexName' => 'events-en_us',
        ],
    ];
}
```

**Data Structure**:
```json
{
    "objectID": "evt-2026-001",
    "title": "Tech Conference 2026",
    "description": "Annual technology conference",
    "start_date": "2026-06-15T09:00:00Z",
    "end_date": "2026-06-17T18:00:00Z",
    "location": "Berlin Convention Center",
    "category": "Technology",
    "price": 499,
    "currency": "EUR",
    "tags": ["conference", "technology", "networking"]
}
```

---

## Advanced Configuration

### Wildcard Patterns

**All Stores, Specific Locale**:
```php
[
    'sourceIdentifier' => 'global-product-catalog',
    'store' => '*',
    'locales' => ['en_US'],
    'indexName' => 'global-catalog-en',
]
```

**Specific Store, All Locales**:
```php
[
    'sourceIdentifier' => 'de-regulations',
    'store' => 'DE',
    'locales' => ['*'],
    'indexName' => 'regulations-de',
]
```

**All Stores, All Locales**:
```php
[
    'sourceIdentifier' => 'universal-icons',
    'store' => '*',
    'locales' => ['*'],
    'indexName' => 'icons-global',
]
```

### Dynamic Index Naming

If you need dynamic index names based on environment:

```php
public function getEntityToIndexMappings(): array
{
    $environment = APPLICATION_ENV; // e.g., 'dev', 'staging', 'prod'

    return [
        [
            'sourceIdentifier' => 'document',
            'store' => 'DE',
            'locales' => ['de_DE'],
            'indexName' => sprintf('%s-documents-de_de', $environment),
        ],
    ];
}
```

### Multiple Indices for Same Entity

You can map the same source identifier to different indices based on store/locale:

```php
public function getEntityToIndexMappings(): array
{
    return [
        // Fashion documents for DE
        [
            'sourceIdentifier' => 'document',
            'store' => 'DE',
            'locales' => ['de_DE'],
            'indexName' => 'fashion-docs-de',
        ],
        // Tech documents for US
        [
            'sourceIdentifier' => 'document',
            'store' => 'US',
            'locales' => ['en_US'],
            'indexName' => 'tech-docs-us',
        ],
    ];
}
```

---

## Related Documentation

- [Spryker Algolia Integration Guide](https://docs.spryker.com/docs/pbc/all/search/latest/base-shop/third-party-integrations/algolia/integrate-algolia)
- [Algolia Index Documentation](https://www.algolia.com/doc/guides/sending-and-managing-data/send-and-update-your-data/)
- [Spryker Search Architecture](https://docs.spryker.com/docs/scos/dev/back-end-development/data-manipulation/data-interaction/search/search.html)

---

## Summary

Custom Entity Index Mapping provides a lightweight way to integrate Algolia search for any entity without complex publisher plugins. Key takeaways:

- **Simple Configuration**: Just define source identifier → index name mappings
- **Flexible**: Supports multi-store, multi-locale scenarios
- **Non-Invasive**: No need to modify core Algolia module
- **Read-Only**: Perfect for externally-managed indices
- **Scalable**: Add as many custom entities as needed

For entities requiring real-time synchronization from Spryker, consider creating proper publisher plugins instead.

