<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\CodingStandard\EasyCodingStandard;

use Cline\CodingStandard\PhpCsFixer\Fixer\AuthorTagFixer;
use Cline\CodingStandard\PhpCsFixer\Fixer\DuplicateDocBlockAfterAttributesFixer;
use Cline\CodingStandard\PhpCsFixer\Fixer\ImportFqcnInAttributeFixer;
use Cline\CodingStandard\PhpCsFixer\Fixer\ImportFqcnInNewFixer;
use Cline\CodingStandard\PhpCsFixer\Fixer\ImportFqcnInPropertyFixer;
use Cline\CodingStandard\PhpCsFixer\Fixer\ImportFqcnInStaticCallFixer;
use Cline\CodingStandard\PhpCsFixer\Fixer\NamespaceFixer;
use Cline\CodingStandard\PhpCsFixer\Fixer\NewArgumentNewlineFixer;
use Cline\CodingStandard\PhpCsFixer\Fixer\PsalmImmutableOnReadonlyClassFixer;
use Cline\CodingStandard\PhpCsFixer\Fixer\RedundantReadonlyPropertyFixer;
use Cline\CodingStandard\PhpCsFixer\Preset\PresetInterface;
use Cline\CodingStandard\PhpCsFixer\Preset\Standard;
use Closure;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\FixerFactory;
use PhpCsFixer\RuleSet\RuleSet;
use PhpCsFixerCustomFixers\Fixers as PhpCsFixerCustomFixers;
use Symplify\EasyCodingStandard\Config\ECSConfig;

use function is_array;

/**
 * Factory for creating EasyCodingStandard configurations.
 *
 * Provides a streamlined way to create ECS configurations using
 * the centralized PHP-CS-Fixer presets and custom fixers.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class Factory
{
    /**
     * Creates an ECS configuration closure.
     *
     * @param  array<string>                     $paths  Paths to check (e.g., [__DIR__.'/src', __DIR__.'/tests'])
     * @param  array<class-string, list<string>> $skip   Rules to skip, keyed by rule class with array of paths
     * @param  null|PresetInterface              $preset Custom preset to use (defaults to Standard)
     * @param  array<string, mixed>              $rules  Additional rules to merge with preset
     * @return Closure(ECSConfig): void
     */
    public static function create(
        array $paths,
        array $skip = [],
        ?PresetInterface $preset = null,
        array $rules = [],
    ): Closure {
        return static function (ECSConfig $ecsConfig) use ($paths, $skip, $preset, $rules): void {
            $ecsConfig->paths($paths);
            $ecsConfig->parallel();

            if ($skip !== []) {
                $ecsConfig->skip($skip);
            }

            // Get rules from preset
            $preset ??= new Standard();
            $presetRules = [...$preset->rules(), ...$rules];

            // Register built-in php-cs-fixer fixers
            $fixerFactory = new FixerFactory();
            $fixerFactory->registerBuiltInFixers();
            $fixerFactory->registerCustomFixers(
                new PhpCsFixerCustomFixers(),
            );
            $fixerFactory->registerCustomFixers(self::getCustomFixers());

            $ruleSet = new RuleSet($presetRules);
            $fixerFactory->useRuleSet($ruleSet);

            foreach ($fixerFactory->getFixers() as $fixer) {
                $fixerName = $fixer->getName();
                $ruleConfig = $ruleSet->getRuleConfiguration($fixerName);

                if ($fixer instanceof ConfigurableFixerInterface && is_array($ruleConfig)) {
                    $ecsConfig->ruleWithConfiguration($fixer::class, $ruleConfig);
                } else {
                    $ecsConfig->rule($fixer::class);
                }
            }
        };
    }

    /**
     * Gets the list of custom fixers from the coding standard.
     *
     * @return list<\PhpCsFixer\Fixer\FixerInterface>
     */
    private static function getCustomFixers(): array
    {
        return [
            new AuthorTagFixer(),
            new DuplicateDocBlockAfterAttributesFixer(),
            new ImportFqcnInAttributeFixer(),
            new ImportFqcnInNewFixer(),
            new ImportFqcnInPropertyFixer(),
            new ImportFqcnInStaticCallFixer(),
            new NamespaceFixer(),
            new NewArgumentNewlineFixer(),
            new PsalmImmutableOnReadonlyClassFixer(),
            new RedundantReadonlyPropertyFixer(),
        ];
    }
}
