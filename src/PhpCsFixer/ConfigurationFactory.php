<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\CodingStandard\PhpCsFixer;

use Cline\CodingStandard\PhpCsFixer\Fixer\AbstractNameFixer;
use Cline\CodingStandard\PhpCsFixer\Fixer\AuthorTagFixer;
use Cline\CodingStandard\PhpCsFixer\Fixer\DuplicateDocBlockAfterAttributesFixer;
use Cline\CodingStandard\PhpCsFixer\Fixer\ExceptionNameFixer;
use Cline\CodingStandard\PhpCsFixer\Fixer\FinalReadonlyClassFixer;
use Cline\CodingStandard\PhpCsFixer\Fixer\ImportFqcnInAttributeFixer;
use Cline\CodingStandard\PhpCsFixer\Fixer\ImportFqcnInNewFixer;
use Cline\CodingStandard\PhpCsFixer\Fixer\ImportFqcnInPropertyFixer;
use Cline\CodingStandard\PhpCsFixer\Fixer\ImportFqcnInStaticCallFixer;
use Cline\CodingStandard\PhpCsFixer\Fixer\InterfaceNameFixer;
use Cline\CodingStandard\PhpCsFixer\Fixer\NamespaceFixer;
use Cline\CodingStandard\PhpCsFixer\Fixer\NewArgumentNewlineFixer;
use Cline\CodingStandard\PhpCsFixer\Fixer\PsalmImmutableOnReadonlyClassFixer;
use Cline\CodingStandard\PhpCsFixer\Fixer\RedundantReadonlyPropertyFixer;
use Cline\CodingStandard\PhpCsFixer\Fixer\TraitNameFixer;
use Cline\CodingStandard\PhpCsFixer\Fixer\VariableCaseFixer;
use Cline\CodingStandard\PhpCsFixer\Fixer\VersionTagFixer;
use Cline\CodingStandard\PhpCsFixer\Preset\PresetInterface;
use ErickSkrauch\PhpCsFixer\Fixers;
use PhpCsFixer\Config;
use PhpCsFixer\ConfigInterface;
use PhpCsFixer\Finder;
use RuntimeException;

use const PHP_VERSION_ID;

use function array_merge;
use function sprintf;

/**
 * @author Brian Faust <brian@cline.sh>
 * @version 1.0.2
 */
final class ConfigurationFactory
{
    /** @var array<string> */
    private static array $notName = [
        '_ide_helper_actions.php',
        '_ide_helper_models.php',
        '_ide_helper.php',
        '.phpstorm.meta.php',
        '*.blade.php',
    ];

    /** @var array<string> */
    private static array $exclude = [
        'bootstrap/cache',
        'build',
        'node_modules',
        'storage',
    ];

    /**
     * @param array<string, array<string, mixed>|bool> $rules
     */
    public static function createFromRules(array $rules): ConfigInterface
    {
        return new Config()
            ->setFinder(self::finder())
            ->setRules($rules)
            ->setRiskyAllowed(true)
            ->setUsingCache(true);
    }

    /**
     * @param array<string, array<string, mixed>|bool> $overrideRules
     */
    public static function createFromPreset(PresetInterface $preset, array $overrideRules = []): ConfigInterface
    {
        if (PHP_VERSION_ID < $preset->targetPhpVersion()) {
            throw new RuntimeException(sprintf(
                'Current PHP version "%s" is less than targeted PHP version "%s".',
                PHP_VERSION_ID,
                $preset->targetPhpVersion(),
            ));
        }

        return new Config($preset->name())
            ->setUnsupportedPhpVersionAllowed(true)
            ->setFinder(self::finder())
            ->setRules(array_merge($preset->rules(), $overrideRules))
            ->setRiskyAllowed(true)
            ->setUsingCache(true)
            // ->setParallelConfig(ParallelConfigFactory::detect())
            ->registerCustomFixers(
                new Fixers(),
            )
            ->registerCustomFixers(
                new \PhpCsFixerCustomFixers\Fixers(),
            )
            ->registerCustomFixers([
                new AbstractNameFixer(),
                new AuthorTagFixer(),
                new NewArgumentNewlineFixer(),
                new ImportFqcnInNewFixer(),
                new ImportFqcnInAttributeFixer(),
                new ImportFqcnInStaticCallFixer(),
                new ImportFqcnInPropertyFixer(),
                new DuplicateDocBlockAfterAttributesFixer(),
                new ExceptionNameFixer(),
                new FinalReadonlyClassFixer(),
                new InterfaceNameFixer(),
                new NamespaceFixer(),
                new RedundantReadonlyPropertyFixer(),
                new TraitNameFixer(),
                new VariableCaseFixer(),
                new VersionTagFixer(),
                new PsalmImmutableOnReadonlyClassFixer(),
            ]);
    }

    public static function finder(): Finder
    {
        return Finder::create()
            ->notName(self::$notName)
            ->exclude(self::$exclude)
            ->ignoreDotFiles(true)
            ->ignoreVCS(true);
    }
}
