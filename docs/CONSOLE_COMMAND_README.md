# Algolia Module - Console Command for Entity Export

## Overview

This module provides an extensible console command for exporting entities to Algolia index.

## Features

- **Extensible Plugin Architecture**: Add new entity types via plugins
- **Multiple Entity Support**: Export products, CMS pages, and other entities
- **Flexible Filtering**: Filter by store, locale, and other criteria
- **Batch Processing**: Configurable chunk sizes for performance
- **Dry Run Mode**: Preview exports without actual execution
- **User-Friendly Output**: Clear progress and summary information

## Installation

### 1. Register the Console Command

In `src/Pyz/Zed/Console/ConsoleDependencyProvider.php`:

```php
use SprykerEco\Zed\Algolia\Communication\Console\AlgoliaEntityExportConsole;

protected function getConsoleCommands(Container $container): array
{
    $commands = [
        // ... other commands
        new AlgoliaEntityExportConsole(),
    ];

    return $commands;
}
```

### 2. Register Entity Exporter Plugins

In `src/Pyz/Zed/Algolia/AlgoliaDependencyProvider.php`:

```php
<?php

namespace Pyz\Zed\Algolia;

use SprykerEco\Zed\Algolia\AlgoliaDependencyProvider as SprykerEcoAlgoliaDependencyProvider;
use SprykerEco\Zed\Algolia\Communication\Plugin\Algolia\ProductAlgoliaEntityExporterPlugin;
use SprykerEco\Zed\Algolia\Communication\Plugin\Algolia\CmsPageAlgoliaEntityExporterPlugin;

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
            // Add more entity exporters here as needed
        ];
    }
}
```

### 3. Generate Transfers

```bash
vendor/bin/console transfer:generate
```

## Usage

### List Available Entity Types

```bash
vendor/bin/console algolia:entity-export
```

### Export Specific Entity Type

```bash
# Export products
vendor/bin/console algolia:entity-export product

# Export CMS pages
vendor/bin/console algolia:entity-export cms-page
```

### Export with Filters

```bash
# Export products for a specific locale
vendor/bin/console algolia:entity-export product --locale=de_DE

# Export with custom chunk size
vendor/bin/console algolia:entity-export product --chunk-size=50

# Export with store filter
vendor/bin/console algolia:entity-export cms-page --store=DE
```

### Dry Run Mode

```bash
# Preview what would be exported without actually exporting
vendor/bin/console algolia:entity-export product --dry-run
```

### Export All Entity Types

```bash
# Export all registered entity types
vendor/bin/console algolia:entity-export --all

# Export all with filters
vendor/bin/console algolia:entity-export --all --store=DE --locale=de_DE
```

## Creating Custom Entity Exporters

### 1. Create the Plugin

```php
<?php

namespace Pyz\Zed\Algolia\Communication\Plugin\Algolia;

use Generated\Shared\Transfer\AlgoliaExportCriteriaTransfer;
use Generated\Shared\Transfer\AlgoliaExportResultTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use SprykerEco\Zed\Algolia\Dependency\Plugin\AlgoliaEntityExporterPluginInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CategoryAlgoliaEntityExporterPlugin extends AbstractPlugin implements AlgoliaEntityExporterPluginInterface
{
    protected const ENTITY_TYPE = 'category';

    public function getEntityType(): string
    {
        return static::ENTITY_TYPE;
    }

    public function export(
        AlgoliaExportCriteriaTransfer $criteriaTransfer,
        OutputInterface $output
    ): AlgoliaExportResultTransfer {
        $resultTransfer = (new AlgoliaExportResultTransfer())
            ->setEntityType(static::ENTITY_TYPE)
            ->setIsSuccessful(true);

        try {
            if ($criteriaTransfer->getIsDryRun()) {
                $output->writeln('<comment>DRY RUN: Would export categories</comment>');
                return $resultTransfer;
            }

            $output->writeln('Exporting categories...');

            // Your export logic here
            // - Get category IDs
            // - Process in chunks
            // - Trigger events or directly export to Algolia

            $resultTransfer
                ->setTotalCount(100)
                ->setExportedCount(100)
                ->setFailedCount(0);

            $output->writeln('<info>Categories exported successfully</info>');
        } catch (\Exception $exception) {
            $resultTransfer
                ->setIsSuccessful(false)
                ->addMessage($exception->getMessage());
        }

        return $resultTransfer;
    }
}
```

### 2. Register the Plugin

Add it to `AlgoliaDependencyProvider`:

```php
protected function getAlgoliaEntityExporterPlugins(): array
{
    return [
        new ProductAlgoliaEntityExporterPlugin(),
        new CmsPageAlgoliaEntityExporterPlugin(),
        new CategoryAlgoliaEntityExporterPlugin(), // Add here
    ];
}
```

### 3. Use It

```bash
vendor/bin/console algolia:entity-export category
```

## Architecture

### Plugin System

The module uses a plugin-based architecture:

```
AlgoliaIndexExportConsole
    ↓
AlgoliaFacade::exportEntitiesToIndex()
    ↓
AlgoliaEntityExporter
    ↓
AlgoliaEntityExporterPluginInterface[] (Plugin Stack)
    ├── ProductAlgoliaEntityExporterPlugin
    ├── CmsPageAlgoliaEntityExporterPlugin
    └── [Your Custom Plugins]
```

### Plugin Interface

```php
interface AlgoliaEntityExporterPluginInterface
{
    // Returns entity type name (e.g., 'product', 'cms-page')
    public function getEntityType(): string;

    // Performs the actual export
    public function export(
        AlgoliaExportCriteriaTransfer $criteriaTransfer,
        OutputInterface $output
    ): AlgoliaExportResultTransfer;
}
```

### Transfer Objects

**AlgoliaExportCriteriaTransfer**:
- `entityType`: Type of entity to export
- `storeName`: Optional store filter
- `locale`: Optional locale filter
- `chunkSize`: Batch size for processing
- `isDryRun`: Whether to skip actual export

**AlgoliaExportResultTransfer**:
- `entityType`: Type of entity exported
- `totalCount`: Total entities found
- `exportedCount`: Successfully exported
- `failedCount`: Failed exports
- `isSuccessful`: Overall success status
- `messages`: Additional messages

## Configuration

Override in `AlgoliaConfig`:

```php
<?php

namespace Pyz\Zed\Algolia;

use SprykerEco\Zed\Algolia\AlgoliaConfig as SprykerEcoAlgoliaConfig;

class AlgoliaConfig extends SprykerEcoAlgoliaConfig
{
    public function getDefaultExportChunkSize(): int
    {
        return 500; // Override default chunk size
    }
}
```


## Use Cases

1. **Initial Bulk Indexing**: Index all entities when setting up Algolia
2. **Re-indexing**: Re-index after data migrations or fixes
3. **Partial Re-indexing**: Re-index specific stores or locales
4. **Testing**: Dry-run mode for testing export logic
5. **Debugging**: Manual triggering for troubleshooting
6. **Scheduled Jobs**: Run via cron for periodic full exports

## Best Practices

1. **Use Dry Run First**: Test with `--dry-run` before actual export
2. **Appropriate Chunk Sizes**: Balance between memory and performance
3. **Monitor Progress**: Watch console output for issues
4. **Error Handling**: Check export results and handle failures
5. **Combine with Events**: Use console for bulk, events for real-time updates

## Testing

```bash
# Test with dry run
vendor/bin/console algolia:entity-export product --dry-run

# Test single store
vendor/bin/console algolia:entity-export product --store=DE --dry-run

# Test with small chunk size
vendor/bin/console algolia:entity-export product --chunk-size=10 --dry-run
```

## Module Structure

```
SprykerEco/Algolia/
├── src/
│   └── SprykerEco/
│       ├── Shared/
│       │   └── Algolia/
│       │       └── Transfer/
│       │           └── algolia.transfer.xml
│       └── Zed/
│           └── Algolia/
│               ├── Business/
│               │   ├── AlgoliaBusinessFactory.php
│               │   ├── AlgoliaFacade.php
│               │   ├── AlgoliaFacadeInterface.php
│               │   ├── Exporter/
│               │   │   ├── AlgoliaEntityExporter.php
│               │   │   └── AlgoliaEntityExporterInterface.php
│               │   └── Exception/
│               │       └── AlgoliaEntityExporterNotFoundException.php
│               ├── Communication/
│               │   ├── AlgoliaCommunicationFactory.php
│               │   ├── Console/
│               │   │   └── AlgoliaIndexExportConsole.php
│               │   └── Plugin/
│               │       └── Algolia/
│               │           ├── ProductAlgoliaEntityExporterPlugin.php
│               │           └── CmsPageAlgoliaEntityExporterPlugin.php
│               ├── Dependency/
│               │   └── Plugin/
│               │       └── AlgoliaEntityExporterPluginInterface.php
│               ├── AlgoliaConfig.php
│               └── AlgoliaDependencyProvider.php
```

## Future Enhancements

1. **Progress Bars**: Add visual progress indicators
2. **Parallel Processing**: Support multi-process execution
3. **Resume Capability**: Resume interrupted exports
4. **Export Statistics**: Detailed export metrics
5. **Webhook Support**: Trigger webhooks on completion
6. **More Entity Types**: Additional pre-built exporters

## License

Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
Use of this software requires acceptance of the Evaluation License Agreement.
