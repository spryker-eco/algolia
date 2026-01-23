# Algolia Product Publisher Plugins

This directory contains publisher plugins that synchronize product data to Algolia in real-time based on various product-related events.

## Overview

These plugins replace the need to use the Product module's MessageBroker publisher plugins for Algolia. Instead of publishing to a message broker, these plugins directly publish product data to Algolia indices.

## Available Plugins

### 1. AlgoliaProductConcretePublisherPlugin

**Purpose**: Publishes product concrete (variant) data to Algolia when products are created or updated.

**Default Subscribed Events**:
- Product creation/update events (PRODUCT_CONCRETE_PUBLISH, ENTITY_SPY_PRODUCT_CREATE, etc.)
- Product localized attributes changes
- Product images changes
- **Product bundles changes** (if ProductBundleStorage module exists)
- **Product prices changes** (if PriceProduct module exists)
- **Product search data changes** (if ProductSearch module exists)

**Usage**:
```php
// In Pyz\Zed\Publisher\PublisherDependencyProvider::getPublisherPlugins()
new \SprykerEco\Zed\Algolia\Communication\Plugin\Publisher\Product\AlgoliaProductConcretePublisherPlugin(),
```

### 2. AlgoliaProductAbstractPublisherPlugin

**Purpose**: Publishes all concrete products of a product abstract when abstract-level data changes (categories, labels, reviews, etc.).

**Default Subscribed Events**:
- Product abstract updates
- Category assignments
- Product labels
- Reviews
- Images
- **Price changes** (if PriceProduct module exists)

**Usage**:
```php
// In Pyz\Zed\Publisher\PublisherDependencyProvider::getPublisherPlugins()
new \SprykerEco\Zed\Algolia\Communication\Plugin\Publisher\Product\AlgoliaProductAbstractPublisherPlugin(),
```

### 3. AlgoliaProductConcreteUnpublisherPlugin

**Purpose**: Removes deleted products from Algolia indices.

**Default Subscribed Events**:
- PRODUCT_CONCRETE_UNPUBLISH
- ENTITY_SPY_PRODUCT_DELETE

**Usage**:
```php
// In Pyz\Zed\Publisher\PublisherDependencyProvider::getPublisherPlugins()
new \SprykerEco\Zed\Algolia\Communication\Plugin\Publisher\Product\AlgoliaProductConcreteUnpublisherPlugin(),
```

## Configuration

### Default Behavior

All plugins get their subscribed events from `AlgoliaConfig`. The config automatically includes events from optional modules if they exist:

- ✅ **ProductBundleStorage** - Bundle events included if module exists
- ✅ **PriceProduct** - Price events included if module exists
- ✅ **ProductSearch** - Search events included if module exists

This means you don't need to worry about missing dependencies - events are only registered if the corresponding module is installed.

### Customizing Events

You can customize which events trigger publishing by extending `AlgoliaConfig` in your project:

```php
<?php

namespace Pyz\Zed\Algolia;

use SprykerEco\Zed\Algolia\AlgoliaConfig as SprykerEcoAlgoliaConfig;

class AlgoliaConfig extends SprykerEcoAlgoliaConfig
{
    /**
     * @return array<string>
     */
    public function getProductConcreteSubscribedEvents(): array
    {
        // Option 1: Completely override with your own events
        return [
            'Product.product_concrete.publish',
            'Entity.spy_product.update',
            'Entity.spy_price_product.update',
        ];
    }

    /**
     * @return array<string>
     */
    public function getProductAbstractSubscribedEvents(): array
    {
        // Option 2: Extend parent events with additional ones
        $events = parent::getProductAbstractSubscribedEvents();
        $events[] = 'YourCustomModule.your_custom_event';

        return $events;
    }

    /**
     * @return array<string>
     */
    public function getProductConcreteUnpublishSubscribedEvents(): array
    {
        // Option 3: Use parent defaults
        return parent::getProductConcreteUnpublishSubscribedEvents();
    }
}
```

### Optional Module Support

The config uses `class_exists()` checks to conditionally include events:

```php
// In AlgoliaConfig::getProductConcreteSubscribedEvents()
if (class_exists('Spryker\Shared\ProductBundleStorage\ProductBundleStorageConfig')) {
    $events[] = 'ProductBundleStorage.product_bundle.publish';
    // ... more bundle events
}

if (class_exists('Spryker\Zed\PriceProduct\Dependency\PriceProductEvents')) {
    $events[] = 'PriceProduct.price_concrete.publish';
    // ... more price events
}

if (class_exists('Spryker\Zed\ProductSearch\Dependency\ProductSearchEvents')) {
    $events[] = 'Entity.spy_product_search.create';
    // ... more search events
}
```

This means:
- ✅ **No hard dependencies** on optional modules
- ✅ **Graceful degradation** if modules are not installed
- ✅ **Automatic inclusion** when modules are present

## How It Works

1. **Event Detection**: The plugins subscribe to events defined in `AlgoliaConfig`

2. **Data Fetching**: When an event is triggered:
   - Product IDs are extracted from event transfers
   - Full product data (including abstract data) is fetched via `ProductFacade::getProductConcreteCollection()` with `setWithProductAbstractData(true)`
   - For abstract events, all active concrete products are fetched

3. **Publishing**: Product data is sent to Algolia using:
   - `AlgoliaFacade::updateProducts()` for create/update events
   - `AlgoliaFacade::deleteProduct()` for delete events

4. **Indexing**: The AlgoliaFacade handles:
   - Data transformation and mapping
   - Multi-locale/multi-store indexing
   - Sending data to appropriate Algolia indices

## Integration Example

```php
<?php

namespace Pyz\Zed\Publisher;

use Spryker\Zed\Publisher\PublisherDependencyProvider as SprykerPublisherDependencyProvider;
use SprykerEco\Zed\Algolia\Communication\Plugin\Publisher\Product\AlgoliaProductAbstractPublisherPlugin;
use SprykerEco\Zed\Algolia\Communication\Plugin\Publisher\Product\AlgoliaProductConcretePublisherPlugin;
use SprykerEco\Zed\Algolia\Communication\Plugin\Publisher\Product\AlgoliaProductConcreteUnpublisherPlugin;

class PublisherDependencyProvider extends SprykerPublisherDependencyProvider
{
    protected function getPublisherPlugins(): array
    {
        return [
            // ... other publisher plugins

            // Algolia product publishers - events configured via AlgoliaConfig
            new AlgoliaProductConcretePublisherPlugin(),
            new AlgoliaProductAbstractPublisherPlugin(),
            new AlgoliaProductConcreteUnpublisherPlugin(),
        ];
    }
}
```

## Event Consolidation

The plugins are designed to avoid duplication. Events are grouped into two main categories:

- **Product Concrete Events**: Direct changes to product variants (SKU, attributes, prices specific to concrete)
- **Product Abstract Events**: Changes affecting all variants of an abstract (categories, labels, reviews)

The abstract publisher fetches all active concrete products of affected abstracts, ensuring all variants are updated when abstract-level data changes.

## Performance Considerations

- Products are published in the events they're triggered, which happens asynchronously via the queue system
- Multiple events for the same product in a single request are deduplicated by the Publisher module
- Use the `AlgoliaConfig` to limit events if you experience performance issues
- Events from optional modules are only registered if those modules are installed

## Comparison to Message Broker Approach

**Previous approach** (Product MessageBroker Publishers):
- Events → Product module → Message Broker → External consumer

**New approach** (Algolia Publishers):
- Events → Algolia module (via AlgoliaConfig) → Algolia API directly

**Benefits**:
- ✅ Simpler architecture (no message broker needed for Algolia)
- ✅ Faster synchronization (direct API calls)
- ✅ Easier debugging (all logic in one module)
- ✅ Better error handling (immediate feedback)
- ✅ **Configurable events** (via AlgoliaConfig)
- ✅ **Optional module support** (automatic detection)
- ✅ **No hard dependencies** (graceful degradation)
