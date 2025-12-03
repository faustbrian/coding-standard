<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\CodingStandard\Rector;

use Rector\Config\RectorConfig;
use Rector\Configuration\RectorConfigBuilder;
use RectorLaravel\Set\LaravelSetList;
use RectorLaravel\Set\LaravelSetProvider;

/**
 * Factory for creating Rector configurations.
 *
 * Provides a streamlined way to create Rector configurations with
 * sensible defaults for Laravel projects using PHP 8.5.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class Factory
{
    /**
     * Creates a Rector configuration builder.
     *
     * @param array<string>                     $paths         Paths to process (e.g., [__DIR__.'/src', __DIR__.'/tests']).
     * @param array<class-string, list<string>> $skip          Rules to skip, keyed by rule class with array of paths.
     * @param bool                              $withRootFiles Whether to include root PHP files.
     * @param array<string>                     $sets          Additional Rector sets to include.
     * @param bool                              $laravel       Whether to include Laravel-specific rules.
     * @param int                               $maxProcesses  Max parallel processes (default: 8).
     */
    public static function create(
        array $paths,
        array $skip = [],
        bool $withRootFiles = true,
        array $sets = [],
        bool $laravel = true,
        int $maxProcesses = 8,
    ): RectorConfigBuilder {
        $config = RectorConfig::configure()
            ->withPaths($paths)
            ->withSkip($skip)
            ->withPhpSets(php85: true)
            ->withParallel(maxNumberOfProcess: $maxProcesses)
            ->withImportNames(importShortClasses: false, removeUnusedImports: true)
            ->withComposerBased(
                phpunit: true,
                laravel: $laravel,
            )
            ->withPreparedSets(
                deadCode: true,
                codeQuality: true,
                codingStyle: true,
                typeDeclarations: true,
                privatization: true,
                naming: false,
                instanceOf: true,
                earlyReturn: true,
                carbon: true,
                rectorPreset: true,
                phpunitCodeQuality: true,
                doctrineCodeQuality: true,
                symfonyCodeQuality: true,
                symfonyConfigs: true,
            );

        if ($laravel) {
            $config = $config
                ->withSetProviders(LaravelSetProvider::class)
                ->withSets([
                    LaravelSetList::LARAVEL_CODE_QUALITY,
                    LaravelSetList::LARAVEL_COLLECTION,
                    LaravelSetList::LARAVEL_ARRAYACCESS_TO_METHOD_CALL,
                    LaravelSetList::LARAVEL_ARRAY_STR_FUNCTION_TO_STATIC_CALL,
                    LaravelSetList::LARAVEL_CONTAINER_STRING_TO_FULLY_QUALIFIED_NAME,
                    LaravelSetList::LARAVEL_ELOQUENT_MAGIC_METHOD_TO_QUERY_BUILDER,
                    LaravelSetList::LARAVEL_FACADE_ALIASES_TO_FULL_NAMES,
                    LaravelSetList::LARAVEL_FACTORIES,
                    LaravelSetList::LARAVEL_IF_HELPERS,
                    LaravelSetList::LARAVEL_LEGACY_FACTORIES_TO_CLASSES,
                    ...$sets,
                ]);
        } elseif ($sets !== []) {
            $config = $config->withSets($sets);
        }

        if ($withRootFiles) {
            return $config->withRootFiles();
        }

        return $config;
    }
}
