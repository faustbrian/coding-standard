---
title: Basic Usage
description: Get started with the coding standard package for modern PHP projects.
---

## Installation

```bash
composer require cline/coding-standard --dev
```

## EasyCodingStandard (Recommended)

The recommended way to use this package is through EasyCodingStandard (ECS), which wraps PHP-CS-Fixer with parallel processing support.

### Quick Setup

Create an `ecs.php` in your project root:

```php
<?php declare(strict_types=1);

use Cline\CodingStandard\EasyCodingStandard\Factory;

return Factory::create(
    paths: [__DIR__.'/src', __DIR__.'/tests'],
);
```

That's it! The factory provides sensible defaults using the Standard preset.

### With Custom Options

```php
<?php declare(strict_types=1);

use Cline\CodingStandard\EasyCodingStandard\Factory;
use Cline\CodingStandard\PhpCsFixer\Preset\Standard;

return Factory::create(
    paths: [__DIR__.'/src', __DIR__.'/tests'],
    skip: [
        // Skip specific rules for specific paths
        SomeFixer::class => ['src/Legacy/*'],
    ],
    preset: new Standard(),
    rules: [
        // Override or add rules
        'single_line_throw' => true,
    ],
);
```

### Running ECS

```bash
# Check for issues
vendor/bin/ecs check

# Fix issues
vendor/bin/ecs check --fix
```

## Rector (Automated Refactoring)

For automated code upgrades and refactoring, use the Rector factory.

### Quick Setup

Create a `rector.php` in your project root:

```php
<?php declare(strict_types=1);

use Cline\CodingStandard\Rector\Factory;

return Factory::create(
    paths: [__DIR__.'/src', __DIR__.'/tests'],
);
```

### With Custom Options

```php
<?php declare(strict_types=1);

use Cline\CodingStandard\Rector\Factory;

return Factory::create(
    paths: [__DIR__.'/src', __DIR__.'/tests'],
    skip: [
        // Skip specific rules
        SomeRector::class => ['src/Legacy/*'],
    ],
    withRootFiles: true,  // Include root PHP files
    laravel: true,        // Include Laravel-specific rules
    maxProcesses: 8,      // Parallel processing
);
```

### Running Rector

```bash
# Preview changes (dry-run)
vendor/bin/rector --dry-run

# Apply changes
vendor/bin/rector
```

## Direct PHP-CS-Fixer Usage

If you prefer using PHP-CS-Fixer directly (without ECS), you can still use the presets:

```php
<?php declare(strict_types=1);

use Cline\CodingStandard\PhpCsFixer\ConfigurationFactory;
use Cline\CodingStandard\PhpCsFixer\Preset\Standard;

return ConfigurationFactory::createFromPreset(new Standard());
```

## Composer Scripts

Add convenient scripts to your `composer.json`:

```json
{
    "scripts": {
        "lint": "vendor/bin/ecs check --fix",
        "refactor": "rector",
        "test:lint": "vendor/bin/ecs check",
        "test:refactor": "rector --dry-run"
    }
}
```

Then run:

```bash
composer lint           # Fix style issues
composer refactor       # Apply refactorings
composer test:lint      # Check style without fixing
composer test:refactor  # Preview refactorings
```

## Available Presets

The package includes several presets:

- **Standard** - Complete rule set for PHP 8.4+ projects (recommended)
- **PHPDoc** - PHPDoc formatting and standards
- **PHPUnit** - PHPUnit test formatting
- **Ordered** - Import and ordering rules

The `Standard` preset already includes `PHPDoc`, `PHPUnit`, and `Ordered` presets.

## Custom Fixers

This package registers several custom fixers automatically:

- Naming convention fixers (Abstract, Interface, Trait, Exception)
- Import FQCN fixers (new, attributes, static calls, properties)
- Architecture fixers (namespace, author tags, version tags)
- Code quality fixers (duplicate docblocks, readonly classes, variable case)

See [Custom Fixers](/coding-standard/custom-fixers/) for detailed documentation.
