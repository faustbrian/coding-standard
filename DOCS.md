## Table of Contents

1. [Basic Usage](#doc-docs-basic-usage)
2. [Custom Fixers](#doc-docs-custom-fixers)
3. [Presets](#doc-docs-presets)
<a id="doc-docs-basic-usage"></a>

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

See [Custom Fixers](#doc-docs-custom-fixers) for detailed documentation.

<a id="doc-docs-custom-fixers"></a>

## Naming Convention Fixers

### AbstractNameFixer

Enforces that abstract classes follow the `Abstract*` naming pattern.

```php
// ❌ Before
abstract class BaseRepository {}
abstract class RepositoryBase {}

// ✅ After
abstract class AbstractRepository {}
```

**Rule Key:** `Architecture/abstract_name_fixer`

### InterfaceNameFixer

Enforces that interfaces follow the `*Interface` naming pattern.

```php
// ❌ Before
interface Repository {}
interface IRepository {}

// ✅ After
interface RepositoryInterface {}
```

**Rule Key:** `Architecture/interface_name_fixer`

### TraitNameFixer

Enforces that traits follow standard trait naming conventions.

```php
// ❌ Before
trait TimestampsTrait {}

// ✅ After
trait Timestamps {}
```

**Rule Key:** `Architecture/trait_name_fixer`

### ExceptionNameFixer

Enforces that exceptions follow the `*Exception` naming pattern.

```php
// ❌ Before
class InvalidInput {}
class ValidationError {}

// ✅ After
class InvalidInputException {}
class ValidationException {}
```

**Rule Key:** `Architecture/exception_name_fixer`

### VariableCaseFixer

Enforces camelCase naming for variables.

```php
// ❌ Before
$user_name = 'John';
$UserEmail = 'john@example.com';

// ✅ After
$userName = 'John';
$userEmail = 'john@example.com';
```

**Rule Key:** `Architecture/variable_case_fixer`

## Import Fixers

### ImportFqcnInNewFixer

Automatically imports fully qualified class names in `new` expressions.

```php
// ❌ Before
$user = new \App\Models\User();

// ✅ After
use App\Models\User;

$user = new User();
```

**Rule Key:** `Architecture/import_fqcn_in_new_fixer`

### ImportFqcnInAttributeFixer

Automatically imports fully qualified class names in attributes.

```php
// ❌ Before
#[\App\Attributes\Cached(ttl: 3600)]
class UserRepository {}

// ✅ After
use App\Attributes\Cached;

#[Cached(ttl: 3600)]
class UserRepository {}
```

**Rule Key:** `Architecture/import_fqcn_in_attribute_fixer`

### ImportFqcnInStaticCallFixer

Automatically imports fully qualified class names in static method calls.

```php
// ❌ Before
$value = \App\Services\Cache::get('key');

// ✅ After
use App\Services\Cache;

$value = Cache::get('key');
```

**Rule Key:** `Architecture/import_fqcn_in_static_call_fixer`

### ImportFqcnInPropertyFixer

Automatically imports fully qualified class names in property type declarations.

```php
// ❌ Before
class UserController
{
    private \App\Services\UserService $userService;
}

// ✅ After
use App\Services\UserService;

class UserController
{
    private UserService $userService;
}
```

**Rule Key:** `Architecture/import_fqcn_in_property_fixer`

## Code Quality Fixers

### FinalReadonlyClassFixer

Automatically adds `final` modifier to `readonly` classes.

```php
// ❌ Before
readonly class User {}

// ✅ After
final readonly class User {}
```

**Rule Key:** `Architecture/final_readonly_class_fixer`

### RedundantReadonlyPropertyFixer

Removes redundant `readonly` modifiers from properties in `readonly` classes.

```php
// ❌ Before
readonly class User
{
    public readonly string $name;
}

// ✅ After
readonly class User
{
    public string $name;
}
```

**Rule Key:** `Architecture/redundant_readonly_property_fixer`

### DuplicateDocBlockAfterAttributesFixer

Removes duplicate PHPDoc blocks that appear after PHP attributes.

```php
// ❌ Before
#[Route('/users')]
/**
 * @param string $id
 */
public function show(string $id) {}

// ✅ After
#[Route('/users')]
public function show(string $id) {}
```

**Rule Key:** `Architecture/duplicate_docblock_after_attributes_fixer`

### PsalmImmutableOnReadonlyClassFixer

Adds `@psalm-immutable` annotation to `readonly` classes.

```php
// ❌ Before
readonly class User {}

// ✅ After
/**
 * @psalm-immutable
 */
readonly class User {}
```

**Rule Key:** `Architecture/psalm_immutable_on_readonly_class_fixer`

## Documentation Fixers

### AuthorTagFixer

Enforces consistent `@author` tag format in PHPDoc blocks.

```php
// ❌ Before
/**
 * @author John Doe
 */

// ✅ After
/**
 * @author Brian Faust <brian@cline.sh>
 */
```

**Rule Key:** `Architecture/author_tag_fixer`

### VersionTagFixer

Enforces consistent `@version` tag format in PHPDoc blocks.

```php
// ❌ Before
/**
 * @version 1.0
 */

// ✅ After
/**
 * @version 1.0.0
 */
```

**Rule Key:** `Architecture/version_tag_fixer`

### NamespaceFixer

Enforces consistent namespace declarations.

**Rule Key:** `Architecture/namespace_fixer`

## Formatting Fixers

### NewArgumentNewlineFixer

Enforces newlines for constructor arguments in `new` expressions.

```php
// ❌ Before
$user = new User('John', 'Doe', 'john@example.com', 25);

// ✅ After
$user = new User(
    'John',
    'Doe',
    'john@example.com',
    25,
);
```

**Rule Key:** `Architecture/new_argument_newline_fixer`

## Enabling Custom Fixers

All custom fixers are automatically registered when using `ConfigurationFactory::createFromPreset()`. To enable specific fixers:

```php
use Cline\CodingStandard\PhpCsFixer\ConfigurationFactory;
use Cline\CodingStandard\PhpCsFixer\Preset\Standard;

return ConfigurationFactory::createFromPreset(
    new Standard(),
    [
        'Architecture/abstract_name_fixer' => true,
        'Architecture/interface_name_fixer' => true,
        'Architecture/exception_name_fixer' => true,
    ]
);
```

Some fixers are disabled by default in the Standard preset:

```php
// Commented out in Standard preset (disabled by default):
// 'Architecture/abstract_name_fixer' => true,
// 'Architecture/exception_name_fixer' => true,
// 'Architecture/interface_name_fixer' => true,
// 'Architecture/trait_name_fixer' => true,
// 'Architecture/version_tag_fixer' => true,
// 'Architecture/final_readonly_class_fixer' => true,
```

Enable them explicitly if needed:

```php
return ConfigurationFactory::createFromPreset(
    new Standard(),
    [
        'Architecture/abstract_name_fixer' => true,
        'Architecture/exception_name_fixer' => true,
    ]
);
```

<a id="doc-docs-presets"></a>

## Standard Preset

The comprehensive preset for PHP 8.4+ projects. Includes all other presets plus extensive formatting and code quality rules.

**Target PHP Version:** 8.4+

### Usage

```php
<?php

use Cline\CodingStandard\PhpCsFixer\ConfigurationFactory;
use Cline\CodingStandard\PhpCsFixer\Preset\Standard;

return ConfigurationFactory::createFromPreset(new Standard());
```

### What's Included

The Standard preset combines:
- **PHPDoc preset** - All PHPDoc formatting rules
- **PHPUnit preset** - PHPUnit test formatting rules
- **Ordered preset** - Import and class element ordering rules
- **200+ formatting rules** - Comprehensive code style enforcement
- **Custom fixers** - All architectural and naming convention fixers

### Key Rules

#### Code Style
- `strict_comparison` - Enforces strict comparison operators (`===`, `!==`)
- `strict_param` - Enforces strict type checking in built-in functions
- `declare_strict_types` - Requires `declare(strict_types=1)`
- `final_class` - Makes all classes `final` by default
- `global_namespace_import` - Auto-imports classes, functions, and constants

#### Type Safety
- `native_function_invocation` - Optimizes native function calls with namespaced scope
- `nullable_type_declaration_for_default_null_value` - Adds nullable types for `null` defaults
- `modernize_types_casting` - Uses modern type casting syntax

#### Import Management
- `global_namespace_import` - Auto-imports from global namespace
  - `import_classes: true`
  - `import_constants: true`
  - `import_functions: true`

#### Header Comment
Automatically adds copyright header:

```php
/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
```

### Notable Disabled Rules

Some rules are disabled for practical reasons:

```php
'date_time_immutable' => false,
// Disabled: Causes issues with Laravel's DateTime/CarbonInterface conversions

'no_unused_imports' => false,
// Disabled: PCRE2 pattern issues in PHP 8.4/PCRE2 10.44

'simplified_if_return' => false,
'simplified_null_return' => false,
// Disabled: Can reduce code clarity
```

## PHPDoc Preset

Enforces consistent PHPDoc formatting, tag ordering, and alignment.

**Target PHP Version:** 8.2+

### Usage

```php
<?php

use Cline\CodingStandard\PhpCsFixer\ConfigurationFactory;
use Cline\CodingStandard\PhpCsFixer\Preset\PHPDoc;

return ConfigurationFactory::createFromPreset(new PHPDoc());
```

### Key Rules

#### Alignment
```php
'phpdoc_align' => [
    'align' => 'vertical',
    'tags' => ['method', 'param', 'property', 'return', 'throws', 'type', 'var'],
]
```

Aligns PHPDoc tags vertically:

```php
/**
 * @param  string $name  The user name
 * @param  int    $age   The user age
 * @return User           The created user
 */
```

#### Tag Ordering
```php
'phpdoc_order' => [
    'order' => [
        'deprecated',
        'internal',
        'covers',
        'uses',
        'dataProvider',
        'param',
        'throws',
        'return',
    ],
]
```

#### Tag Separation
```php
'phpdoc_separation' => [
    'groups' => [
        ['deprecated', 'link', 'see', 'since'],
        ['author', 'copyright', 'license'],
        ['category', 'package', 'subpackage'],
        ['property', 'property-read', 'property-write'],
        ['param', 'return'],
    ],
]
```

Groups are separated by blank lines:

```php
/**
 * @deprecated Will be removed in v2.0
 * @see UserRepository
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @param string $id
 * @return User|null
 */
```

#### Line Span
```php
'phpdoc_line_span' => [
    'const' => 'multi',
    'method' => 'multi',
    'property' => 'multi',
]
```

Forces multi-line PHPDoc for constants, methods, and properties.

### Custom PHPDoc Fixers

Includes additional fixers from `kubawerlos/php-cs-fixer-custom-fixers`:
- `PhpdocNoSuperfluousParamFixer` - Removes unnecessary `@param` tags
- `PhpdocParamTypeFixer` - Ensures correct param type format
- `PhpdocSelfAccessorFixer` - Standardizes `@return self` usage
- `PhpdocSingleLineVarFixer` - Formats single-line `@var` tags
- `PhpdocTypesCommaSpacesFixer` - Formats comma spacing in union types
- `PhpdocTypesTrimFixer` - Trims extra whitespace from types

## PHPUnit Preset

Enforces modern PHPUnit testing standards and naming conventions.

**Target PHP Version:** 8.2+

### Usage

```php
<?php

use Cline\CodingStandard\PhpCsFixer\ConfigurationFactory;
use Cline\CodingStandard\PhpCsFixer\Preset\PHPUnit;

return ConfigurationFactory::createFromPreset(new PHPUnit());
```

### Key Rules

#### Test Method Naming
```php
'php_unit_method_casing' => [
    'case' => 'snake_case',
]
```

Enforces snake_case for test methods:

```php
// ❌ Before
public function testUserCanLogin() {}

// ✅ After
public function test_user_can_login() {}
```

#### Data Providers
```php
'php_unit_data_provider_name' => true,
'php_unit_data_provider_return_type' => true,
'php_unit_data_provider_static' => true,
```

Enforces data provider conventions:

```php
public static function userDataProvider(): array
{
    return [
        'valid user' => ['John', 25],
        'young user' => ['Jane', 18],
    ];
}
```

#### Assertion Style
```php
'php_unit_test_case_static_method_calls' => [
    'call_type' => 'self',
    'methods' => [],
]
```

Uses `self::` for static assertions:

```php
// ❌ Before
$this->assertTrue($value);
static::assertTrue($value);

// ✅ After
self::assertTrue($value);
```

#### Test Annotation
```php
'php_unit_test_annotation' => [
    'style' => 'prefix',
]
```

Prefers `test` prefix over `@test` annotation:

```php
// ❌ Before
/**
 * @test
 */
public function user_can_login() {}

// ✅ After
public function test_user_can_login() {}
```

### Custom PHPUnit Fixers

- `PhpUnitAssertArgumentsOrderFixer` - Enforces correct assertion argument order
- `PhpUnitDedicatedAssertFixer` - Uses dedicated assertion methods
- `PhpUnitNoUselessReturnFixer` - Removes unnecessary returns in tests

## Ordered Preset

Enforces consistent ordering of imports, class elements, attributes, interfaces, and traits.

**Target PHP Version:** 8.4+

### Usage

```php
<?php

use Cline\CodingStandard\PhpCsFixer\ConfigurationFactory;
use Cline\CodingStandard\PhpCsFixer\Preset\Ordered;

return ConfigurationFactory::createFromPreset(new Ordered());
```

### Key Rules

#### Import Ordering
```php
'ordered_imports' => [
    'imports_order' => ['class', 'const', 'function'],
    'sort_algorithm' => 'alpha',
]
```

Orders imports alphabetically by type:

```php
// Classes first
use App\Models\User;
use App\Services\AuthService;

// Constants second
use const PHP_VERSION_ID;

// Functions last
use function array_merge;
use function sprintf;
```

#### Class Element Ordering
```php
'ordered_class_elements' => [
    'order' => [
        'use_trait',
        'case',
        'constant_public',
        'constant_protected',
        'constant_private',
        'property_public_static',
        'property_public',
        'property_public_readonly',
        // ... (complete ordering)
        'construct',
        'destruct',
        'magic',
        'phpunit',
        'method_public_static',
        'method_public',
        // ...
    ],
]
```

Enforces strict class member ordering:

```php
final class User
{
    use HasFactory;                           // Traits first

    public const STATUS_ACTIVE = 'active';    // Public constants
    private const MAX_ATTEMPTS = 3;           // Private constants

    public static int $count = 0;             // Public static properties
    public string $name;                      // Public properties
    private string $password;                 // Private properties

    public function __construct(string $name) // Constructor
    {
        $this->name = $name;
    }

    public static function create(): self    // Public static methods
    {
        return new self('');
    }

    public function getName(): string        // Public methods
    {
        return $this->name;
    }

    private function hash(): string          // Private methods
    {
        return password_hash($this->password, PASSWORD_DEFAULT);
    }
}
```

#### Attribute Ordering (Laravel Data)

Orders Spatie Laravel Data attributes consistently:

```php
'ordered_attributes' => [
    'order' => [
        // Availability attributes
        Nullable::class,
        Required::class,
        // Complementary validation
        Accepted::class,
        Email::class,
        Max::class,
        // Behavior attributes
        Computed::class,
        Hidden::class,
        // ...
    ],
]
```

Example:

```php
final readonly class UserData extends Data
{
    public function __construct(
        #[Required]
        #[Email]
        #[Max(255)]
        public string $email,
    ) {}
}
```

#### Interface Ordering
```php
'ordered_interfaces' => [
    'direction' => 'ascend',
    'order' => 'alpha',
]
```

Orders implemented interfaces alphabetically:

```php
// ✅ After
final class User implements Authenticatable, Authorizable, JsonSerializable
{
}
```

## Combining Presets

### Use Standard for Everything

The Standard preset already includes all other presets:

```php
<?php

use Cline\CodingStandard\PhpCsFixer\ConfigurationFactory;
use Cline\CodingStandard\PhpCsFixer\Preset\Standard;

return ConfigurationFactory::createFromPreset(new Standard());
```

### Manual Combination (Advanced)

For custom combinations, merge preset rules manually:

```php
<?php

use Cline\CodingStandard\PhpCsFixer\ConfigurationFactory;
use Cline\CodingStandard\PhpCsFixer\Preset\PHPDoc;
use Cline\CodingStandard\PhpCsFixer\Preset\Ordered;

$rules = [
    ...new PHPDoc()->rules(),
    ...new Ordered()->rules(),
    // Add custom overrides
    'single_line_throw' => true,
];

return ConfigurationFactory::createFromRules($rules);
```

## Preset Comparison

| Feature | Standard | PHPDoc | PHPUnit | Ordered |
|---------|----------|--------|---------|---------|
| PHP Version | 8.4+ | 8.2+ | 8.2+ | 8.4+ |
| PHPDoc Rules | ✅ | ✅ | ❌ | ❌ |
| PHPUnit Rules | ✅ | ❌ | ✅ | ❌ |
| Ordering Rules | ✅ | ❌ | ❌ | ✅ |
| Custom Fixers | ✅ | ❌ | ❌ | ❌ |
| Style Rules | ✅ | ❌ | ❌ | ❌ |
| Import Optimization | ✅ | ❌ | ❌ | ✅ |

## Overriding Preset Rules

All presets support rule overrides:

```php
<?php

use Cline\CodingStandard\PhpCsFixer\ConfigurationFactory;
use Cline\CodingStandard\PhpCsFixer\Preset\Standard;

return ConfigurationFactory::createFromPreset(
    new Standard(),
    [
        'final_class' => false,              // Disable final class enforcement
        'phpdoc_align' => false,             // Disable PHPDoc alignment
        'php_unit_method_casing' => [
            'case' => 'camel_case',          // Override test method casing
        ],
    ]
);
```
