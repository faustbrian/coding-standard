<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\CodingStandard\PhpCsFixer\Fixer;

use Override;
use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\FileSpecificCodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use RuntimeException;
use SplFileInfo;

use const T_NAMESPACE;
use const T_STRING;
use const T_WHITESPACE;

use function array_key_exists;
use function dirname;
use function file_exists;
use function file_get_contents;
use function getcwd;
use function in_array;
use function is_array;
use function json_decode;
use function mb_rtrim;
use function mb_strlen;
use function mb_substr;
use function str_replace;
use function str_starts_with;
use function throw_if;
use function throw_unless;

/**
 * @author Brian Faust <brian@cline.sh>
 * @version 1.0.0
 */
final class NamespaceFixer extends AbstractFixer
{
    /** @var array<string, string> */
    private readonly array $psr4Config;

    public function __construct()
    {
        $this->psr4Config = $this->loadPsr4Config();
    }

    #[Override()]
    public function getName(): string
    {
        return 'Architecture/namespace_fixer';
    }

    #[Override()]
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Updates namespace based on PSR-4 configuration from composer.json',
            [
                new FileSpecificCodeSample(
                    '<?php
namespace Wrong\Namespace;',
                    new SplFileInfo(__FILE__),
                    // 'Updates namespace to match PSR-4 autoload configuration.',
                ),
            ],
        );
    }

    /**
     * @param Tokens<Token> $tokens
     */
    #[Override()]
    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_NAMESPACE);
    }

    /**
     * @param Tokens<Token> $tokens
     */
    #[Override()]
    protected function applyFix(SplFileInfo $file, Tokens $tokens): void
    {
        $expectedNamespace = $this->determineNamespace($file);

        if ($expectedNamespace === null) {
            return;
        }

        $namespacePosition = $tokens->getNextTokenOfKind(0, [[T_NAMESPACE]]);

        if ($namespacePosition === null) {
            return;
        }

        // Find the namespace name tokens
        $namespaceEndPosition = $tokens->getNextTokenOfKind($namespacePosition, [';']);

        if ($namespaceEndPosition === null) {
            return;
        }

        // Replace existing namespace with new one
        $tokens->clearRange($namespacePosition, $namespaceEndPosition);
        $tokens->insertAt($namespacePosition, [
            new Token([T_NAMESPACE, 'namespace']),
            new Token([T_WHITESPACE, ' ']),
            new Token([T_STRING, $expectedNamespace]),
            new Token(';'),
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function loadPsr4Config(): array
    {
        $composerPath = $this->findComposerJson();

        throw_if(in_array($composerPath, [null, '', '0'], true), RuntimeException::class, 'composer.json not found in any parent directory');

        $composerJson = file_get_contents($composerPath);

        throw_if($composerJson === false, RuntimeException::class, 'Unable to read composer.json');

        $composer = json_decode($composerJson, true);

        throw_unless(is_array($composer), RuntimeException::class, 'Invalid composer.json format');
        throw_unless(array_key_exists('autoload', $composer), RuntimeException::class, 'No autoload configuration found in composer.json');
        throw_unless(is_array($composer['autoload']), RuntimeException::class, 'Invalid autoload configuration in composer.json');
        throw_unless(array_key_exists('psr-4', $composer['autoload']), RuntimeException::class, 'No PSR-4 autoload configuration found in composer.json');
        throw_unless(is_array($composer['autoload']['psr-4']), RuntimeException::class, 'Invalid PSR-4 autoload configuration in composer.json');

        /** @var array{autoload: array{psr-4: array<string, string>}} $composer */

        // Convert relative paths to absolute paths based on composer.json location
        $baseDir = dirname($composerPath);

        /** @var array<string, string> $psr4Config */
        $psr4Config = [];

        foreach ($composer['autoload']['psr-4'] as $namespace => $path) {
            $psr4Config[$namespace] = $this->normalizePath($baseDir.'/'.$path);
        }

        return $psr4Config;
    }

    private function findComposerJson(): ?string
    {
        $dir = getcwd();

        if ($dir === false) {
            return null;
        }

        while ($dir !== '/' && $dir !== '') {
            $composerPath = $dir.'/composer.json';

            if (file_exists($composerPath)) {
                return $composerPath;
            }

            $parentDir = dirname($dir);

            if ($parentDir === $dir) {
                break;
            }

            $dir = $parentDir;
        }

        return null;
    }

    private function normalizePath(string $path): string
    {
        return mb_rtrim(str_replace('\\', '/', $path), '/');
    }

    private function determineNamespace(SplFileInfo $file): ?string
    {
        $realPath = $file->getRealPath();

        if ($realPath === false) {
            return null;
        }

        $this->normalizePath($realPath);
        $dirPath = $this->normalizePath(dirname($realPath));

        foreach ($this->psr4Config as $namespace => $directory) {
            if (str_starts_with($dirPath, $directory)) {
                $subPath = mb_substr($dirPath, mb_strlen($directory) + 1);
                $subNamespace = str_replace('/', '\\', $subPath);

                // Remove trailing slash from namespace if it exists
                return mb_rtrim($namespace, '\\').($subNamespace !== '' && $subNamespace !== '0' ? '\\'.$subNamespace : '');
            }
        }

        return null;
    }
}
