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
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;

use const T_DOC_COMMENT;

use function array_map;
use function explode;
use function implode;
use function in_array;
use function mb_strlen;
use function mb_trim;
use function preg_match;
use function preg_split;
use function wordwrap;

/**
 * @author Brian Faust <brian@cline.sh>
 * @version 1.0.0
 */
final class PhpdocLineLengthFixer extends AbstractFixer
{
    private const int MAX_LINE_LENGTH = 80;

    #[Override()]
    public function getName(): string
    {
        return 'Architecture/phpdoc_line_length_fixer';
    }

    #[Override()]
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Wrap long prose lines in PHPDoc blocks to 80 columns.',
            [
                new CodeSample(
                    <<<'PHP'
<?php
/**
 * Execute the deletion workflow for the pricing route distance row. Validates
 * the request payload, resolves the target aggregate, applies idempotency and
 * lifecycle-state rules, and returns a lifecycle result.
 */
final class Example
{
}
PHP,
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
        return $tokens->isTokenKindFound(T_DOC_COMMENT);
    }

    /**
     * @param Tokens<Token> $tokens
     */
    #[Override()]
    protected function applyFix(SplFileInfo $file, Tokens $tokens): void
    {
        foreach ($tokens as $index => $token) {
            if (!$token->isGivenKind(T_DOC_COMMENT)) {
                continue;
            }

            $content = $token->getContent();
            $wrapped = $this->wrapDocComment($content);

            if ($wrapped === $content) {
                continue;
            }

            $tokens[$index] = new Token([T_DOC_COMMENT, $wrapped]);
        }
    }

    private function wrapDocComment(string $content): string
    {
        $lines = preg_split('/\R/u', $content);

        if ($lines === false) {
            return $content;
        }

        foreach ($lines as $index => $line) {
            $lines[$index] = $this->wrapLine($line);
        }

        return implode("\n", $lines);
    }

    private function wrapLine(string $line): string
    {
        if ($this->shouldSkipLine($line)) {
            return $line;
        }

        if (!preg_match('/^(\s*\*\s?)(.*)$/', $line, $matches)) {
            return $line;
        }

        $prefix = $matches[1];
        $text = mb_trim($matches[2]);
        $availableWidth = self::MAX_LINE_LENGTH - mb_strlen($prefix);

        if ($availableWidth < 1 || mb_strlen($line) <= self::MAX_LINE_LENGTH) {
            return $line;
        }

        $wrapped = wordwrap($text, $availableWidth, "\n", true);

        return implode("\n", array_map(
            static fn (string $wrappedLine): string => $prefix.$wrappedLine,
            explode("\n", $wrapped),
        ));
    }

    private function shouldSkipLine(string $line): bool
    {
        $trimmedLine = mb_trim($line);

        if (in_array($trimmedLine, ['', '/**', '*/'], true)) {
            return true;
        }

        return preg_match('/^\s*\*\s*@/', $line) === 1;
    }
}
