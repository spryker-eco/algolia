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
- [Publisher Plugins](#publisher-plugins)
  - [Product Publisher Plugins](#product-publisher-plugins)
  - [CMS Page Publisher Plugins](#cms-page-publisher-plugins)
- [Console Commands](#console-commands)
- [Configuration](#configuration)
- [Troubleshooting](#troubleshooting)

## Installation

```bash
composer require spryker-eco/algolia
```

**Configure Algolia credentials** in your config files:
```php
// config/Shared/config_default.php
$config[AlgoliaConstants::APPLICATION_ID] = getenv('ALGOLIA_APPLICATION_ID');
$config[AlgoliaConstants::ADMIN_API_KEY] = getenv('ALGOLIA_ADMIN_API_KEY');
$config[AlgoliaConstants::SEARCH_ONLY_API_KEY] = getenv('ALGOLIA_SEARCH_ONLY_API_KEY');
$config[AlgoliaConstants::TENANT_IDENTIFIER] = 'project_name_production';
$config[AlgoliaConstants::IS_ACTIVE] = true;
```

---

## Publisher Plugins

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

**Usage**:
```php
// In Pyz\Zed\Publisher\PublisherDependencyProvider::getPublisherPlugins()
new \SprykerEco\Zed\Algolia\Communication\Plugin\Publisher\Product\AlgoliaProductConcretePublisherPlugin(),
```

#### 2. AlgoliaProductAbstractPublisherPlugin

**Purpose**: Publishes all concrete products of a product abstract when abstract-level data changes.

**Default Subscribed Events**:
- Product abstract updates
- Category assignments
- Product labels
- Reviews
- Images
- Price changes (if PriceProduct exists)

**Usage**:
```php
new \SprykerEco\Zed\Algolia\Communication\Plugin\Publisher\Product\AlgoliaProductAbstractPublisherPlugin(),
```

#### 3. AlgoliaProductConcreteDeletePublisherPlugin

**Purpose**: Removes deleted products from Algolia indices.

**Default Subscribed Events**:
- PRODUCT_CONCRETE_UNPUBLISH
- ENTITY_SPY_PRODUCT_DELETE

**Usage**:
```php
new \SprykerEco\Zed\Algolia\Communication\Plugin\Publisher\Product\AlgoliaProductConcreteDeletePublisherPlugin(),
```

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

**Usage**:
```php
new \SprykerEco\Zed\Algolia\Communication\Plugin\Publisher\CmsPage\AlgoliaCmsPagePublisherPlugin(),
```

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

**Usage**:
```php
new \SprykerEco\Zed\Algolia\Communication\Plugin\Publisher\CmsPage\AlgoliaCmsPageVersionPublisherPlugin(),
```

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

## Console Commands

---

## Console Commands

### Step 1: Register Console Command

File: `src/Pyz/Zed/Console/ConsoleDependencyProvider.php`

```php
<?php

namespace Pyz\Zed\Console;

use Spryker\Zed\Console\ConsoleDependencyProvider as SprykerConsoleDependencyProvider;
use SprykerEco\Zed\Algolia\Communication\Console\AlgoliaEntityExportConsole;

class ConsoleDependencyProvider extends SprykerConsoleDependencyProvider
{
    protected function getConsoleCommands(Container $container): array
    {
        $commands = [
            // ... existing commands
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
    protected function getAlgoliaEntityExporterPlugins(): array
    {
        return [
            new ProductAlgoliaEntityExporterPlugin(),
            new CmsPageAlgoliaEntityExporterPlugin(),
        ];
    }
}
```

### Step 3: Usage Examples

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

### Step 4: Schedule Automatic Exports (Recommended)

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

### Default Event Subscriptions

All publisher plugins get their subscribed events from `AlgoliaConfig`. The config automatically includes events from optional modules if they exist:

**For Products:**
- ✅ ProductBundleStorage - Bundle events (if module exists)
- ✅ PriceProduct - Price events (if module exists)
- ✅ ProductSearch - Search events (if module exists)
- ✅ ProductLabel - Label events (if module exists)
- ✅ ProductReview - Review events (if module exists)

**For CMS Pages:**
- ✅ CMS - All CMS page and version events

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

### Available Configuration Methods

**Product Events:**
- `getProductConcreteSubscribedEvents()` - Product variant events
- `getProductAbstractSubscribedEvents()` - Product abstract events
- `getProductConcreteUnpublishSubscribedEvents()` - Delete events

**CMS Page Events:**
- `getCmsPageUpdateSubscribedEvents()` - Page update events
- `getCmsPageVersionPublishSubscribedEvents()` - Version publish events
- `getCmsPageDeleteSubscribedEvents()` - Page delete events

---

## Architecture

### Data Flow

```
Spryker Events (Back Office/API changes)
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

## Step 2: Configure Entity Exporter Plugins

### Create Project-Level Dependency Provider

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

## Step 3: Generate Transfers

```bash
vendor/bin/console transfer:generate
```

## Step 4: Verify Installation

```bash
# List available commands (should show algolia:index-export)
vendor/bin/console | grep algolia

# Show available entity types
vendor/bin/console algolia:index-export

# Test with dry run
vendor/bin/console algolia:index-export product --dry-run
```

## Step 5: Usage Examples

```bash
# Export products
vendor/bin/console algolia:index-export product

# Export products for specific store
vendor/bin/console algolia:index-export product --store=DE

# Export CMS pages
vendor/bin/console algolia:index-export cms-page

# Export all entity types
vendor/bin/console algolia:index-export --all

# Export with custom chunk size
vendor/bin/console algolia:index-export product --chunk-size=200

# Dry run to preview
vendor/bin/console algolia:index-export product --dry-run --store=DE
```

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
4. Enable debug logging in config

---

## Migration from MessageBroker

If migrating from MessageBroker-based Algolia publishing:

### Step 1: Remove Old Plugins

```php
// Remove from Pyz\Zed\Publisher\PublisherDependencyProvider
// - CmsPageVersionPublishedMessageBrokerPublisherPlugin
// - CmsPageUpdateMessageBrokerPublisherPlugin
```

### Step 2: Add New Algolia Plugins

```php
// Add to Pyz\Zed\Publisher\PublisherDependencyProvider
new AlgoliaCmsPagePublisherPlugin(),
new AlgoliaCmsPageVersionPublisherPlugin(),
```

### Step 3: Verify

- No data migration needed - data structure remains the same
- Test with a CMS page update in Back Office
- Check Algolia dashboard for indexed content

### Benefits of Migration

✅ Direct integration (no MessageBroker overhead)
✅ Simpler architecture
✅ Better performance
✅ Batch deletion support
✅ Complete locale-specific content extraction

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
- Batch deletion for multiple pages
- Locale-specific content extracted once

### General
- All plugins check `AlgoliaConfig::getIsActive()` before subscribing
- If Algolia disabled, no events processed
- Initial export uses configurable batch sizes

---

## Support

For issues or questions:
- Check [Spryker documentation](https://docs.spryker.com)
- Review [Algolia documentation](https://www.algolia.com/doc/)
- Contact Spryker support

## License

This module is licensed under the same license as Spryker Commerce OS.
