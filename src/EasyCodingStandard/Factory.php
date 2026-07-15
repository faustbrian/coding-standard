<?php declare(strict_types=1);

namespace Cline\CodingStandard\EasyCodingStandard;

use Cline\CodingStandard\PhpCsFixer\CopyrightHeader;
use Cline\CodingStandard\PhpCsFixer\Fixer\AuthorTagFixer;
use Cline\CodingStandard\PhpCsFixer\Fixer\DuplicateDocBlockAfterAttributesFixer;
use Cline\CodingStandard\PhpCsFixer\Fixer\ImportFqcnInAttributeFixer;
use Cline\CodingStandard\PhpCsFixer\Fixer\ImportFqcnInNewFixer;
use Cline\CodingStandard\PhpCsFixer\Fixer\ImportFqcnInPropertyFixer;
use Cline\CodingStandard\PhpCsFixer\Fixer\ImportFqcnInStaticCallFixer;
use Cline\CodingStandard\PhpCsFixer\Fixer\NamespaceFixer;
use Cline\CodingStandard\PhpCsFixer\Fixer\NewArgumentNewlineFixer;
use Cline\CodingStandard\PhpCsFixer\Fixer\PhpdocLineLengthFixer;
use Cline\CodingStandard\PhpCsFixer\Fixer\PsalmImmutableOnReadonlyClassFixer;
use Cline\CodingStandard\PhpCsFixer\Fixer\RedundantReadonlyPropertyFixer;
use Cline\CodingStandard\PhpCsFixer\Fixer\RemoveAuthorTagFixer;
use Cline\CodingStandard\PhpCsFixer\Fixer\RemoveHeaderCommentFixer;
use Cline\CodingStandard\PhpCsFixer\Fixer\RemoveVersionTagFixer;
use Cline\CodingStandard\PhpCsFixer\Preset\PresetInterface;
use Cline\CodingStandard\PhpCsFixer\Preset\Standard;
use Closure;
use PHP_CodeSniffer\Sniffs\Sniff;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\Fixer\Phpdoc\PhpdocAnnotationWithoutDotFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocSeparationFixer;
use PhpCsFixer\FixerFactory;
use PhpCsFixer\RuleSet\RuleSet;
use PhpCsFixerCustomFixers\Fixers as PhpCsFixerCustomFixers;
use SlevomatCodingStandard\Sniffs\ControlStructures\EarlyExitSniff;
use SlevomatCodingStandard\Sniffs\PHP\DisallowDirectMagicInvokeCallSniff;
use SlevomatCodingStandard\Sniffs\PHP\RequireNowdocSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\UselessConstantTypeHintSniff;
use Symplify\CodingStandard\Fixer\Spacing\StandaloneLinePromotedPropertyFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\Configuration\ECSConfigBuilder;

use function is_array;

/**
 * Factory for creating EasyCodingStandard configurations.
 *
 * Provides a streamlined way to create ECS configurations using the
 * centralized PHP-CS-Fixer presets and custom fixers.
 */
final class Factory
{
    /**
     * Creates a fluent ECS config builder for direct use in `ecs.php`.
     *
     * @param array<string>                                                             $paths  Paths to check (e.g., [__DIR__.'/src', __DIR__.'/tests']).
     * @param array<class-string<FixerInterface>|int<0, max>, null|list<string>|string> $skip   Rules to skip, keyed by rule class with array of paths.
     * @param null|PresetInterface                                                      $preset Custom preset to use (defaults to Standard).
     * @param array<string, array<string, mixed>|bool>                                  $rules  Additional rules to merge with preset.
     */
    public static function configure(
        array $paths,
        array $skip = [],
        ?PresetInterface $preset = null,
        array $rules = [],
        ?CopyrightHeader $copyrightHeader = null,
    ): ECSConfigBuilder {
        $resolvedRules = [
            ...self::defaultRules($preset, $copyrightHeader),
            ...$rules,
        ];

        ['skip' => $resolvedSkip, 'rules' => $ruleClasses, 'configuredRules' => $configuredRules] = self::resolveConfiguration(
            $skip,
            $preset,
            $resolvedRules,
        );

        $ecsConfigBuilder = ECSConfig::configure()
            ->withPaths($paths)
            ->withParallel();

        if ($resolvedSkip !== []) {
            $ecsConfigBuilder->withSkip($resolvedSkip);
        }

        if ($ruleClasses !== []) {
            $ecsConfigBuilder->withRules($ruleClasses);
        }

        foreach ($configuredRules as $checkerClass => $configuration) {
            $ecsConfigBuilder->withConfiguredRule($checkerClass, $configuration);
        }

        return $ecsConfigBuilder;
    }

    /**
     * Creates an ECS configuration closure.
     *
     * @param array<string>                                                             $paths  Paths to check (e.g., [__DIR__.'/src', __DIR__.'/tests']).
     * @param array<class-string<FixerInterface>|int<0, max>, null|list<string>|string> $skip   Rules to skip, keyed by rule class with array of paths.
     * @param null|PresetInterface                                                      $preset Custom preset to use (defaults to Standard).
     * @param array<string, array<string, mixed>|bool>                                  $rules  Additional rules to merge with preset.
     *
     * @return Closure(ECSConfig): void
     */
    public static function create(
        array $paths,
        array $skip = [],
        ?PresetInterface $preset = null,
        array $rules = [],
        ?CopyrightHeader $copyrightHeader = null,
    ): Closure {
        $resolvedRules = [
            ...self::defaultRules($preset, $copyrightHeader),
            ...$rules,
        ];

        return static function (ECSConfig $ecsConfig) use ($paths, $skip, $preset, $resolvedRules): void {
            $ecsConfig->paths($paths);
            $ecsConfig->parallel();

            ['skip' => $resolvedSkip, 'rules' => $ruleClasses, 'configuredRules' => $configuredRules] = self::resolveConfiguration(
                $skip,
                $preset,
                $resolvedRules,
            );

            if ($resolvedSkip !== []) {
                $ecsConfig->skip($resolvedSkip);
            }

            foreach ($ruleClasses as $checkerClass) {
                $ecsConfig->rule($checkerClass);
            }

            foreach ($configuredRules as $checkerClass => $configuration) {
                $ecsConfig->ruleWithConfiguration($checkerClass, $configuration);
            }
        };
    }

    /**
     * @return array<string, array<string, mixed>|bool>
     */
    private static function defaultRules(
        ?PresetInterface $preset,
        ?CopyrightHeader $copyrightHeader,
    ): array {
        if ($preset instanceof PresetInterface || !$copyrightHeader instanceof CopyrightHeader) {
            return [];
        }

        return [
            'header_comment' => [
                'comment_type' => 'PHPDoc',
                'header' => $copyrightHeader->render(),
                'location' => 'after_declare_strict',
                'separate' => 'both',
            ],
            'Architecture/remove_header_comment_fixer' => false,
        ];
    }

    /**
     * @param array<class-string<FixerInterface>|int<0, max>, null|list<string>|string> $skip
     * @param array<string, array<string, mixed>|bool>                                  $resolvedRules
     *
     * @return array{
     *     skip: array<class-string<FixerInterface>|int<0, max>, null|list<string>|string>,
     *     rules: list<class-string<FixerInterface|Sniff>>,
     *     configuredRules: array<class-string<FixerInterface|Sniff>, array<string, mixed>>
     * }
     */
    private static function resolveConfiguration(
        array $skip,
        ?PresetInterface $preset,
        array $resolvedRules,
    ): array {
        $preset ??= new Standard();
        $presetRules = [...$preset->rules(), ...$resolvedRules];

        $fixerFactory = new FixerFactory();
        $fixerFactory->registerBuiltInFixers();
        $fixerFactory->registerCustomFixers(
            new PhpCsFixerCustomFixers(),
        );
        $fixerFactory->registerCustomFixers(self::getCustomFixers());

        $ruleSet = new RuleSet($presetRules);
        $fixerFactory->useRuleSet($ruleSet);

        /** @var list<class-string<FixerInterface|Sniff>> $ruleClasses */
        $ruleClasses = [];

        /** @var array<class-string<FixerInterface|Sniff>, array<string, mixed>> $configuredRules */
        $configuredRules = [];

        foreach ($fixerFactory->getFixers() as $fixer) {
            $fixerName = $fixer->getName();
            $ruleConfig = $ruleSet->getRuleConfiguration($fixerName);

            if ($fixer instanceof ConfigurableFixerInterface && is_array($ruleConfig)) {
                /** @var array<string, mixed> $ruleConfig */
                $configuredRules[$fixer::class] = $ruleConfig;

                continue;
            }

            $ruleClasses[] = $fixer::class;
        }

        $ruleClasses[] = StandaloneLinePromotedPropertyFixer::class;
        $ruleClasses[] = DisallowDirectMagicInvokeCallSniff::class;
        $ruleClasses[] = EarlyExitSniff::class;
        $ruleClasses[] = RequireNowdocSniff::class;
        $ruleClasses[] = UselessConstantTypeHintSniff::class;

        return [
            'skip' => [
                ...$skip,
                ...self::defaultSkip(),
            ],
            'rules' => $ruleClasses,
            'configuredRules' => $configuredRules,
        ];
    }

    /**
     * @return array<class-string<FixerInterface>, null>
     */
    private static function defaultSkip(): array
    {
        return [
            // TODO: Fix ImportFqcnInPropertyFixer array_key_exists bug
            ImportFqcnInPropertyFixer::class => null,
            // Conflicts with PhpdocOrderFixer (lowercases/removes dots)
            PhpdocAnnotationWithoutDotFixer::class => null,
            // Conflicts with PhpdocOrderFixer (different blank line rules between annotations)
            PhpdocSeparationFixer::class => null,
        ];
    }

    /**
     * Gets the list of custom fixers from the coding standard.
     *
     * @return list<FixerInterface>
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
            new PhpdocLineLengthFixer(),
            new PsalmImmutableOnReadonlyClassFixer(),
            new RedundantReadonlyPropertyFixer(),
            new RemoveAuthorTagFixer(),
            new RemoveHeaderCommentFixer(),
            new RemoveVersionTagFixer(),
        ];
    }
}
