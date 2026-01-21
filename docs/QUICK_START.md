# Algolia Console Command - Quick Reference

## Installation (3 Steps)

### 1. Generate Transfers
```bash
vendor/bin/console transfer:generate
```

### 2. Register Console Command
**File:** `src/Pyz/Zed/Console/ConsoleDependencyProvider.php`

```php
use SprykerEco\Zed\Algolia\Communication\Console\AlgoliaEntityExportConsole;

protected function getConsoleCommands(Container $container): array
{
    return [
        new AlgoliaEntityExportConsole(),
        // ... other commands
    ];
}
```

### 3. Register Plugins
**File:** `src/Pyz/Zed/Algolia/AlgoliaDependencyProvider.php`
```php
<?php
namespace Pyz\Zed\Algolia;

use SprykerEco\Zed\Algolia\AlgoliaDependencyProvider as SprykerEcoAlgoliaDependencyProvider;
use SprykerEco\Zed\Algolia\Communication\Plugin\Algolia\ProductAlgoliaEntityExporterPlugin;
use SprykerEco\Zed\Algolia\Communication\Plugin\Algolia\CmsPageAlgoliaEntityExporterPlugin;

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

## Usage Examples

```bash
# Show available entity types
vendor/bin/console algolia:index-export

# Export products
vendor/bin/console algolia:index-export product

# Export CMS pages
vendor/bin/console algolia:index-export cms-page

# Export with store filter
vendor/bin/console algolia:index-export product --store=DE

# Export with locale filter
vendor/bin/console algolia:index-export product --locale=de_DE

# Export with custom chunk size
vendor/bin/console algolia:index-export product --chunk-size=200

# Dry run (preview only)
vendor/bin/console algolia:index-export product --dry-run

# Export all entity types
vendor/bin/console algolia:index-export --all

# Export all with filters
vendor/bin/console algolia:index-export --all --store=DE --dry-run
```

## Command Options

| Option | Short | Description |
|--------|-------|-------------|
| `--all` | `-a` | Export all registered entity types |
| `--store` | `-s` | Filter by store reference (e.g., DE, US) |
| `--locale` | `-l` | Filter by locale (e.g., de_DE, en_US) |
| `--chunk-size` | `-c` | Number of entities per batch (default: 100) |
| `--dry-run` | `-d` | Preview without actual export |

## File Locations

### Core Files (in vendor/spryker-eco/algolia)
```
src/SprykerEco/Zed/Algolia/
├── Communication/
│   ├── Console/
│   │   └── AlgoliaIndexExportConsole.php              # Main command
│   └── Plugin/Algolia/
│       ├── ProductAlgoliaEntityExporterPlugin.php     # Product exporter
│       └── CmsPageAlgoliaEntityExporterPlugin.php     # CMS page exporter
├── Business/
│   └── Exporter/
│       ├── AlgoliaEntityExporter.php                  # Core exporter
│       └── AlgoliaEntityExporterInterface.php
├── Dependency/Plugin/
│   └── AlgoliaEntityExporterPluginInterface.php       # Plugin interface
└── AlgoliaDependencyProvider.php                      # Plugin registration
```

### Project Files (to create in src/Pyz)
```
src/Pyz/Zed/
├── Console/
│   └── ConsoleDependencyProvider.php                  # Register command here
└── Algolia/
    └── AlgoliaDependencyProvider.php                  # Register plugins here
```

## Adding New Entity Types

### 1. Create Plugin
**File:** `src/Pyz/Zed/Algolia/Communication/Plugin/Algolia/CategoryAlgoliaEntityExporterPlugin.php`
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

        // Your export logic here
        $output->writeln('Exporting categories...');

        return $resultTransfer;
    }
}
```

### 2. Register Plugin
In `src/Pyz/Zed/Algolia/AlgoliaDependencyProvider.php`:
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
vendor/bin/console algolia:index-export category
```

## Troubleshooting

### Command not found
```bash
# Clear cache
vendor/bin/console cache:empty-all
```

### No entity types available
- Check that plugins are registered in `AlgoliaDependencyProvider`
- Ensure you created the project-level `AlgoliaDependencyProvider`

### Transfer not found
```bash
# Regenerate transfers
vendor/bin/console transfer:generate
```

## Documentation Files

- **CONSOLE_COMMAND_README.md** - Complete documentation
- **INTEGRATION.md** - Detailed integration guide
- **SUMMARY.md** - Architecture and usage examples
- **CONSOLE_COMMAND_IMPLEMENTATION.md** - Implementation summary

## Support

For detailed documentation, see the files listed above in:
```
vendor/spryker-eco/algolia/
```
