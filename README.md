# Algolia Module
[![Latest Stable Version](https://poser.pugx.org/spryker-eco/algolia/v/stable.svg)](https://packagist.org/packages/spryker-eco/algolia)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.3-8892BF.svg)](https://php.net/)

The Algolia module provides seamless integration between Spryker Commerce OS and Algolia search service, enabling real-time synchronization of products and CMS pages to Algolia indices for fast, relevant search experiences.

## Features

- 🔄 **Real-time synchronization** of products and CMS pages to Algolia
- 🎯 **Event-driven architecture** using Spryker's Publisher module
- 🌍 **Multi-locale and multi-store** support
- 📦 **Batch export** capabilities for initial data sync
- 🔍 **Search API integration** for frontend and backend
- ⚙️ **Configurable event subscriptions** per entity type
- 🏗️ **Modular design** with optional module support

## Table of Contents

- [Installation](#installation)
- [Real-time Synchronization](#real-time-synchronization)
  - [Product Publisher Plugins](#product-publisher-plugins)
  - [CMS Page Publisher Plugins](#cms-page-publisher-plugins)
- [Full Indexing](#full-indexing)
- [Configuration](#configuration)
- [Migration from ACP Algolia App](#migration-from-acp-algolia-app)
- [Troubleshooting](#troubleshooting)
- [Development](#development)

## Installation

```bash
composer require spryker-eco/algolia
```

**Configure Algolia credentials** in your config files:
```php
// config/Shared/config_default.php or config_local.php
use SprykerEco\Shared\Algolia\AlgoliaConstants;
$config[AlgoliaConstants::APPLICATION_ID] = getenv('ALGOLIA_APPLICATION_ID');
$config[AlgoliaConstants::ADMIN_API_KEY] = getenv('ALGOLIA_ADMIN_API_KEY');
$config[AlgoliaConstants::SEARCH_ONLY_API_KEY] = getenv('ALGOLIA_SEARCH_ONLY_API_KEY');
$config[AlgoliaConstants::TENANT_IDENTIFIER] = 'john'; // Add if you use one Algolia account for multiple environments, default is "production".
```


### Step 1: Enable Console Command

File: `src/Pyz/Zed/Console/ConsoleDependencyProvider.php`

```php
<?php

namespace Pyz\Zed\Console;

use Spryker\Zed\Console\ConsoleDependencyProvider as SprykerConsoleDependencyProvider;
use SprykerEco\Zed\Algolia\Communication\Console\AlgoliaEntityExportConsole;

class ConsoleDependencyProvider extends SprykerConsoleDependencyProvider
{
    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return array<\Symfony\Component\Console\Command\Command>
     */
    protected function getConsoleCommands(Container $container): array
    {
        $commands = [
            // ... existing commands

            // Add Algolia export command
            new AlgoliaEntityExportConsole(),
        ];

        return $commands;
    }
}
```

### Step 2: Configure Entity Exporter Plugins

File: `src/Pyz/Zed/Algolia/AlgoliaDependencyProvider.php`

```php
<?php

namespace Pyz\Zed\Algolia;

use SprykerEco\Zed\Algolia\AlgoliaDependencyProvider as SprykerEcoAlgoliaDependencyProvider;
use SprykerEco\Zed\Algolia\Communication\Plugin\Algolia\CmsPageAlgoliaEntityExporterPlugin;
use SprykerEco\Zed\Algolia\Communication\Plugin\Algolia\ProductAlgoliaEntityExporterPlugin;

class AlgoliaDependencyProvider extends SprykerEcoAlgoliaDependencyProvider
{
    /**
     * @return array<\SprykerEco\Zed\Algolia\Dependency\Plugin\AlgoliaEntityExporterPluginInterface>
     */
    protected function getAlgoliaEntityExporterPlugins(): array
    {
        return [
            new ProductAlgoliaEntityExporterPlugin(),
            new CmsPageAlgoliaEntityExporterPlugin(),
            // Add more entity exporters here
        ];
    }
}
```

### Step 3: Configure Search Adapter Plugin

File: `src/Pyz/Client/Search/SearchDependencyProvider.php`

```php
<?php

namespace Pyz\Client\Search;

use Spryker\Client\Search\SearchDependencyProvider as SprykerSearchDependencyProvider;
use SprykerEco\Client\Algolia\Plugin\Search\AlgoliaSearchAdapterPlugin;

class SearchDependencyProvider extends SprykerSearchDependencyProvider
{
    /**
     * @return array<\Spryker\Client\SearchExtension\Dependency\Plugin\SearchAdapterPluginInterface>
     */
    protected function getClientAdapterPlugins(): array
    {
        return [
            new AlgoliaSearchAdapterPlugin(),
            // ... other search adapters
        ];
    }
}
```

### Step 4: Configure Catalog Search Query Plugins

>Note: Also requires `\Pyz\Shared\Algolia\AlgoliaConfig::isSearchInFrontendEnabledForProducts()` to be set to `true`.

>Note 2: Integration heavily depends on SearchHttp module plugins, so they have to be also enabled in `src/Pyz/Client/Catalog/CatalogDependencyProvider.php`,
> [see the integration guide](https://docs.spryker.com/docs/pbc/all/search/latest/base-shop/third-party-integrations/algolia/integrate-algolia#configure-modules-and-their-behavior).

File: `src/Pyz/Client/Catalog/CatalogDependencyProvider.php`

```php
<?php

namespace Pyz\Client\Catalog;

use Spryker\Client\Catalog\CatalogDependencyProvider as SprykerCatalogDependencyProvider;
use SprykerEco\Client\Algolia\Plugin\Search\AlgoliaSearchQueryPlugin;
use SprykerEco\Client\Algolia\Plugin\Search\AlgoliaSuggestionSearchQueryPlugin;
use SprykerEco\Client\Algolia\Plugin\Search\AlgoliaProductConcreteSearchQueryPlugin;

class CatalogDependencyProvider extends SprykerCatalogDependencyProvider
{
    /**
     * @return array<\Spryker\Client\SearchExtension\Dependency\Plugin\QueryInterface>
     */
    protected function createCatalogSearchQueryPluginVariants(): array
    {
        return [
            new AlgoliaSearchQueryPlugin(),
        ];
    }

    /**
     * @return array<\Spryker\Client\SearchExtension\Dependency\Plugin\QueryInterface>
     */
    protected function createSuggestionQueryPluginVariants(): array
    {
        return [
            new AlgoliaSuggestionSearchQueryPlugin(),
        ];
    }

    /**
     * @return array<\Spryker\Client\SearchExtension\Dependency\Plugin\QueryInterface>
     */
    protected function createProductConcreteCatalogSearchQueryPluginVariants(): array
    {
        return [
            new AlgoliaProductConcreteSearchQueryPlugin(),
        ];
    }
}
```

### Step 5: Configure CMS Page Search Query Plugin (Optional)

>Note: Also requires `\Pyz\Shared\Algolia\AlgoliaConfig::isSearchInFrontendEnabledForCmsPages()` to be set to `true`.

>Note 2: Integration heavily depends on SearchHttp module plugins, so they have to be also enabled in
> `src/Pyz/Client/SearchHttp/SearchHttpDependencyProvider.php` and `src/Pyz/Client/CmsPageSearch/CmsPageSearchDependencyProvider.php`,
 [see the integration guide](https://docs.spryker.com/docs/pbc/all/search/latest/base-shop/third-party-integrations/algolia/integrate-algolia#configure-the-cmspagesearch-module).


File: `src/Pyz/Client/CmsPageSearch/CmsPageSearchDependencyProvider.php`

```php
<?php

namespace Pyz\Client\CmsPageSearch;

use Generated\Shared\Transfer\SearchContextTransfer;
use Spryker\Client\CmsPageSearch\CmsPageSearchConfig;
use Spryker\Client\CmsPageSearch\CmsPageSearchDependencyProvider as SprykerCmsPageSearchDependencyProvider;
use SprykerEco\Client\Algolia\Plugin\Search\AlgoliaSearchQueryPlugin;

class CmsPageSearchDependencyProvider extends SprykerCmsPageSearchDependencyProvider
{
    /**
     * @return array<\Spryker\Client\SearchExtension\Dependency\Plugin\QueryInterface>
     */
    protected function getCmsPageSearchQueryPlugins(): array
    {
        return [
            new AlgoliaSearchQueryPlugin(
                (new SearchContextTransfer())
                    ->setSourceIdentifier(CmsPageSearchConfig::SOURCE_IDENTIFIER_CMS_PAGE),
            ),
            // ... other search query plugins
        ];
    }
}
```

### Step 6: Generate Transfers

```bash
vendor/bin/console transfer:generate
```

## Step 7: Verify Installation

```bash
# List available commands (should show algolia:entity-export)
vendor/bin/console | grep algolia

# Show available entity types
vendor/bin/console algolia:entity-export

# Test with dry run
vendor/bin/console algolia:entity-export product --dry-run
```

## Step 8: Usage Examples

```bash
# Export products
vendor/bin/console algolia:entity-export product

# Export products for specific locale
vendor/bin/console algolia:entity-export product --locale=en_US

# Export CMS pages
vendor/bin/console algolia:entity-export cms-page

# Export CMS pages only from one store
vendor/bin/console algolia:entity-export cms-page --store=DE

# Export all entity types
vendor/bin/console algolia:entity-export --all

# Export with custom chunk size
vendor/bin/console algolia:entity-export product --chunk-size=200
```

---

## Real-time Synchronization

### Product Publisher Plugins

Located in: `SprykerEco\Zed\Algolia\Communication\Plugin\Publisher\Product\`

#### 1. AlgoliaProductConcretePublisherPlugin

**Purpose**: Publishes product concrete (variant) data to Algolia when products are created or updated.

**Default Subscribed Events**:
- Product creation/update events
- Product localized attributes changes
- Product images changes
- Product bundles changes (if ProductBundleStorage exists)
- Product prices changes (if PriceProduct exists)
- Product search data changes (if ProductSearch exists)

#### 2. AlgoliaProductAbstractPublisherPlugin

**Purpose**: Publishes all concrete products of a product abstract when abstract-level data changes.

**Default Subscribed Events**:
- Product abstract updates
- Category assignments
- Product labels
- Reviews
- Images
- Price changes (if PriceProduct exists and enabled in the configuration)

#### 3. AlgoliaProductConcreteDeletePublisherPlugin

**Purpose**: Removes deleted products from Algolia indices.

**Default Subscribed Events**:
- PRODUCT_CONCRETE_UNPUBLISH
- ENTITY_SPY_PRODUCT_DELETE

---

### CMS Page Publisher Plugins

Located in: `SprykerEco\Zed\Algolia\Communication\Plugin\Publisher\CmsPage\`

#### 1. AlgoliaCmsPagePublisherPlugin

**Purpose**: Publishes CMS page data to Algolia when pages are created or updated.

**Default Subscribed Events**:
- ENTITY_SPY_CMS_PAGE_UPDATE

**Behavior**:
- Fetches full CMS page data including latest version
- Checks if page is active AND searchable before publishing
- Extracts locale-specific flattened CMS content
- Sends complete page data to Algolia for indexing
- Removes pages from all relevant indices if page is inactive or not searchable

#### 2. AlgoliaCmsPageVersionPublisherPlugin

**Purpose**: Publishes CMS pages when new versions are created or published.

**Default Subscribed Events**:
- CMS_VERSION_PUBLISH
- ENTITY_SPY_CMS_VERSION_CREATE

**Behavior**:
- Maps CMS version IDs to CMS page IDs
- Fetches CMS page and version data
- Extracts full page content with locale-specific data
- Publishes to Algolia with version metadata

---

### Complete Integration Example

```php
<?php

namespace Pyz\Zed\Publisher;

use Spryker\Zed\Publisher\PublisherDependencyProvider as SprykerPublisherDependencyProvider;
use SprykerEco\Zed\Algolia\Communication\Plugin\Publisher\Product\AlgoliaProductAbstractPublisherPlugin;
use SprykerEco\Zed\Algolia\Communication\Plugin\Publisher\Product\AlgoliaProductConcretePublisherPlugin;
use SprykerEco\Zed\Algolia\Communication\Plugin\Publisher\Product\AlgoliaProductConcreteDeletePublisherPlugin;
use SprykerEco\Zed\Algolia\Communication\Plugin\Publisher\CmsPage\AlgoliaCmsPagePublisherPlugin;
use SprykerEco\Zed\Algolia\Communication\Plugin\Publisher\CmsPage\AlgoliaCmsPageVersionPublisherPlugin;
use SprykerEco\Zed\Algolia\Communication\Plugin\Publisher\CmsPage\AlgoliaCmsPageDeletePublisherPlugin;

class PublisherDependencyProvider extends SprykerPublisherDependencyProvider
{
    protected function getPublisherPlugins(): array
    {
        return [
            // Algolia product publishers
            new AlgoliaProductConcretePublisherPlugin(),
            new AlgoliaProductAbstractPublisherPlugin(),
            new AlgoliaProductConcreteDeletePublisherPlugin(),

            // Algolia CMS page publishers
            new AlgoliaCmsPagePublisherPlugin(),
            new AlgoliaCmsPageVersionPublisherPlugin(),
            new AlgoliaCmsPageDeletePublisherPlugin(),
        ];
    }
}
```

---

## Full Indexing

### Usage Examples

```bash
# Export all products to Algolia
console algolia:entity:export product

# Export all CMS pages
console algolia:entity:export cms-page --store=DE

# Export for specific store
console algolia:entity:export product --locale=en_US

# Export with custom chunk size
console algolia:entity:export product --chunk-size=200

```

### Schedule Automatic Exports (Recommended)

For periodic full re-indexing, add a cron job to export entities to Algolia on a scheduled basis.

File: `config/Zed/cronjobs/jenkins.php`

```php
/* Algolia - Weekly full export */
$jobs[] = [
    'name' => 'algolia-export-products',
    'command' => $logger . '$PHP_BIN vendor/bin/console algolia:entity:export product',
    'schedule' => '0 2 * * 0',
    'enable' => true,
];

$jobs[] = [
    'name' => 'algolia-export-cms-pages',
    'command' => $logger . '$PHP_BIN vendor/bin/console algolia:entity:export cms-page',
    'schedule' => '30 2 * * 0',
    'enable' => true,
];
```

**Schedule explanation:**
- `0 2 * * 0` - Runs at 2:00 AM every Sunday (weekly)
- `30 2 * * 0` - Runs at 2:30 AM every Sunday (weekly)

**Note:** These cron jobs complement the real-time publisher plugins. The publishers handle incremental updates,
while the cron jobs ensure full data consistency by performing periodic complete exports.

---

## Configuration

### Available Configuration Methods

- `getIsActive()` - Enable/disable Algolia integration

**Product Events:**
- `getProductConcreteSubscribedEvents()` - Product variant events
- `getProductAbstractSubscribedEvents()` - Product abstract events
- `getProductConcreteUnpublishSubscribedEvents()` - Delete events

**CMS Page Events:**
- `getCmsPageUpdateSubscribedEvents()` - Page update events
- `getCmsPageVersionPublishSubscribedEvents()` - Version publish events

**Search:**
- `isSearchInFrontendEnabledForProducts()` - Enable product search in frontend
- `isSearchInFrontendEnabledForCmsPages()` - Enable CMS page search in frontend

**Insights & Analytics & Personalization:**
- `getIsPersonalizationEnabled()` - Enable/disable Algolia Personalization for search. This feature requires a premium Algolia plan.
- `getProjectMappingFacets()` - Facet names mapping for Algolia Insights event tracking (via TraceableEventWidget).


### Default Event Subscriptions

All publisher plugins get their subscribed events from `AlgoliaConfig`. The config automatically includes events from optional modules if they exist:

**For Products:**
- All product abstract and product concrete events
- ProductBundle - Bundle events (if module exists)
- PriceProduct - Price events (if module exists)
- ProductLabel - Label events (if module exists)
- ProductReview - Review events (if module exists)

**For CMS Pages:**
- CMS - All CMS page and version events

### Customizing Event Subscriptions

Extend `AlgoliaConfig` in your project to customize events:

```php
<?php

namespace Pyz\Zed\Algolia;

use SprykerEco\Zed\Algolia\AlgoliaConfig as SprykerEcoAlgoliaConfig;

class AlgoliaConfig extends SprykerEcoAlgoliaConfig
{
    public function getProductConcreteSubscribedEvents(): array
    {
        // Completely override events
        return [
            'Product.product_concrete.publish',
            'Entity.spy_product.update',
        ];
    }

    public function getCmsPageUpdateSubscribedEvents(): array
    {
        // Extend parent events
        $events = parent::getCmsPageUpdateSubscribedEvents();
        $events[] = 'YourCustom.custom_event';
        return $events;
    }

    public function getDefaultExportChunkSize(): int
    {
        return 500; // Custom chunk size for exports
    }
}
```

---

## Architecture

### Data Flow

```
Spryker Events (Back Office/API changes/Data Import)
           ↓
Publisher Module (Queue-based processing)
           ↓
Algolia Publisher Plugins
    ├── Product Publishers
    └── CMS Page Publishers
           ↓
AlgoliaFacade → Mappers → Indexers → API Client
           ↓
Algolia Search Service
```

### Event Consolidation

**Products:**
- Concrete events: Direct changes to variants
- Abstract events: Changes affecting all variants
- The abstract publisher fetches all active concrete products

**CMS Pages:**
- Page update events: Direct entity changes
- Version publish events: New version creation
- Delete events: Page removal (unpublish)
- Both update and version plugins ensure pages stay current

---

## Troubleshooting

### No entity types available

**Problem**: "No entity exporters are registered"

**Solution**:
1. Ensure plugins are registered in `AlgoliaDependencyProvider::getAlgoliaEntityExporterPlugins()`
2. Check the dependency provider is in `Pyz` namespace if extended
3. Clear cache: `console cache:empty-all`

### Transfer not found

**Problem**: `Class 'Generated\Shared\Transfer\AlgoliaExportCriteriaTransfer' not found`

**Solution**:
```bash
console transfer:generate
```

### Events not triggering

**Problem**: Changes not appearing in Algolia

**Solution**:
1. Check `AlgoliaConfig::getIsActive()` returns `true`
2. Verify publisher plugins are registered in `PublisherDependencyProvider`
3. Check queue workers are running:
   ```bash
   console queue:task:start publish
   ```
4. Debug publishing with Xdebug `docker/sdk console -x queue:task:start publish` or using logs.


### Search requests are failing

**Problem**: Search queries return errors or no results

**Solution**:
1. Verify Algolia credentials in config are correct
2. Ensure indices exist in Algolia dashboard
3. Disable personalization `getIsPersonalizationEnabled()` if you use not premium plan.

---

## Migration from ACP Algolia App

If migrating from MessageBroker-based [Algolia ACP App](https://docs.spryker.com/docs/pbc/all/search/latest/base-shop/third-party-integrations/algolia/algolia):

### Step 1: Remove Old Plugins

#### Remove from `src/Pyz/Zed/Publisher/PublisherDependencyProvider.php`

```php
// - CmsPageVersionPublishedMessageBrokerPublisherPlugin
// - CmsPageUpdateMessageBrokerPublisherPlugin
// - ProductAbstractUpdatedMessageBrokerPublisherPlugin
// - ProductConcreteCreatedMessageBrokerPublisherPlugin
// - ProductConcreteDeletedMessageBrokerPublisherPlugin
// - ProductConcreteExportedMessageBrokerPublisherPlugin
// - ProductConcreteUpdatedMessageBrokerPublisherPlugin
```

#### Remove from `src/Pyz/Zed/MessageBroker/MessageBrokerDependencyProvider.php`

```php
// - SearchEndpointMessageHandlerPlugin
// - ProductExportMessageHandlerPlugin
// - CmsPageMessageHandlerPlugin
```

#### Update in `src/Pyz/Client/Search/SearchDependencyProvider.php`

- Replace `SearchHttpSearchAdapterPlugin` with `AlgoliaSearchAdapterPlugin`

- Remove:
```php
// - SearchHttpSearchContextExpanderPlugin
```

#### Update in `src/Pyz/Client/Catalog/CatalogDependencyProvider.php`

Replace them SearchHttp plugins with Algolia equivalents:

- Replace `SearchHttpQueryPlugin` with `AlgoliaSearchQueryPlugin`
- Replace `SuggestionSearchHttpQueryPlugin` with `AlgoliaSuggestionSearchQueryPlugin`
- Replace `ProductConcreteSearchHttpQueryPlugin` with `AlgoliaProductConcreteSearchQueryPlugin`

#### Update in `src/Pyz/Client/CmsPageSearch/CmsPageSearchDependencyProvider.php`

- Replace `SearchHttpQueryPlugin` with `AlgoliaSearchQueryPlugin`

### Step 2: Add New Algolia Plugins

Follow all integration steps from the [Installation](#installation) section.

### Step 3: Verify

- No data migration needed - data structure remains the same
- Do full re-index using console command
- Test with products update in Back Office
- Test with a CMS page update in Back Office
- Check Algolia dashboard for indexed content

### Benefits of Migration

✅ Direct integration (no MessageBroker overhead)
✅ Simpler architecture
✅ Better performance
✅ Batch indexing support
✅ Configuration and extensibility

---

## Performance Considerations

### Products
- Published asynchronously via queue system
- Multiple events for same product are deduplicated
- Events from optional modules only registered if installed
- Use `AlgoliaConfig` to limit events if needed

### CMS Pages
- Published asynchronously via queue system
- Only active AND searchable pages indexed
- Not searchable or inactive pages removed from indices

### General
- All plugins check `AlgoliaConfig::getIsActive()` before subscribing
- If Algolia disabled, no events processed
- Initial export uses configurable batch sizes

---

## Support

For issues or questions:
- Check [Spryker documentation](https://docs.spryker.com)
- Check [Spryker ACP Algolia app documentation](https://docs.spryker.com/docs/pbc/all/search/latest/base-shop/third-party-integrations/algolia/integrate-algolia#configure-modules-and-their-behavior)
- Review [Algolia documentation](https://www.algolia.com/doc/)
- Contact Spryker support

## Development

To check/fix code style and run static analysis, use:

```bash
composer cs-fix # can be used standalone
composer phpstan # only works together with Spryker project (uses autoloader from it)
```

for test execution check details in [tests/README.md](tests/README.md) file.


## License

This module is licensed under the same license as [Spryker Commerce OS](LICENSE).
