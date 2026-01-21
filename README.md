# Algolia Module
[![Latest Stable Version](https://poser.pugx.org/spryker-eco/algolia/v/stable.svg)](https://packagist.org/packages/spryker-eco/algolia)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.3-8892BF.svg)](https://php.net/)

Algolia module provides integration with Algolia search service.

## Installation

```
composer require spryker-eco/algolia
```

# Integration Guide: Algolia Console Command

## Step 1: Register Console Command

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

## Troubleshooting

### No entity types available

**Problem**: "No entity exporters are registered"

**Solution**:
1. Ensure plugins are registered in `AlgoliaDependencyProvider::getAlgoliaEntityExporterPlugins()`
2. Check that the dependency provider is in the correct namespace (Pyz if extended)

### Transfer not found

**Problem**: `Class 'Generated\Shared\Transfer\AlgoliaExportCriteriaTransfer' not found`

**Solution**:
```bash
vendor/bin/console transfer:generate
```

## Advanced Configuration

### Custom Chunk Size

File: `src/Pyz/Zed/Algolia/AlgoliaConfig.php`

```php
<?php

namespace Pyz\Zed\Algolia;

use SprykerEco\Zed\Algolia\AlgoliaConfig as SprykerEcoAlgoliaConfig;

class AlgoliaConfig extends SprykerEcoAlgoliaConfig
{
    public function getDefaultExportChunkSize(): int
    {
        return 500; // Custom default chunk size
    }
}
```

### Add Custom Entity Exporter

1. Create plugin implementing `AlgoliaEntityExporterPluginInterface`
2. Register in `AlgoliaDependencyProvider::getAlgoliaEntityExporterPlugins()`
3. Use: `vendor/bin/console algolia:index-export your-entity-type`
