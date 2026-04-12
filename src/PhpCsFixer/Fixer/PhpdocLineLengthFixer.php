<?php declare(strict_types=1);

namespace Cline\CodingStandard\PhpCsFixer\Fixer;

use Override;
use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;

use const PHP_INT_MAX;
use const PREG_SPLIT_NO_EMPTY;
use const T_DOC_COMMENT;

use function array_fill;
use function array_map;
use function array_push;
use function array_slice;
use function count;
use function implode;
use function in_array;
use function intdiv;
use function mb_strlen;
use function mb_trim;
use function min;
use function preg_match;
use function preg_split;
use function str_contains;

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
 * Execute the deletion workflow for the pricing route distance row.
 * Validates the request payload, resolves the target aggregate,
 * applies idempotency and lifecycle-state rules, and returns a
 * lifecycle result.
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

    private function extractLineText(string $line): string
    {
        if (!preg_match('/^\s*\*\s?(.*)$/', $line, $matches)) {
            return mb_trim($line);
        }

        return mb_trim($matches[1]);
    }

    private function wrapDocComment(string $content): string
    {
        $lines = preg_split('/\R/u', $content);

        if ($lines === false) {
            return $content;
        }

        $wrappedLines = [];
        $paragraphLines = [];

        foreach ($lines as $line) {
            if ($this->isWrapCandidateLine($line)) {
                $paragraphLines[] = $line;

                continue;
            }

            if ($paragraphLines !== []) {
                array_push($wrappedLines, ...$this->wrapParagraph($paragraphLines));
                $paragraphLines = [];
            }

            $wrappedLines[] = $line;
        }

        if ($paragraphLines !== []) {
            array_push($wrappedLines, ...$this->wrapParagraph($paragraphLines));
        }

        return implode("\n", $wrappedLines);
    }

    /**
     * @param list<string> $paragraphLines
     *
     * @return list<string>
     */
    private function wrapParagraph(array $paragraphLines): array
    {
        $firstLine = $paragraphLines[0];

        if (!preg_match('/^(\s*\*\s?)(.*)$/', $firstLine, $matches)) {
            return $paragraphLines;
        }

        $prefix = $matches[1];
        $availableWidth = self::MAX_LINE_LENGTH - mb_strlen($prefix);

        if ($availableWidth < 1) {
            return $paragraphLines;
        }

        $text = implode(' ', array_map(
            $this->extractLineText(...),
            $paragraphLines,
        ));

        return array_map(
            static fn (string $wrappedLine): string => $prefix.$wrappedLine,
            $this->wrapText($text, $availableWidth),
        );
    }

    /**
     * @return list<string>
     */
    private function wrapText(string $text, int $availableWidth): array
    {
        $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);

        if ($words === false || $words === []) {
            return [$text];
        }

        return $this->buildOptimalLines($words, $availableWidth);
    }

    /**
     * @param list<string> $words
     *
     * @return list<string>
     */
    private function buildOptimalLines(array $words, int $availableWidth): array
    {
        $wordCount = count($words);
        $costs = array_fill(0, $wordCount + 1, PHP_INT_MAX);
        $breaks = array_fill(0, $wordCount, null);
        $costs[$wordCount] = 0;

        for ($start = $wordCount - 1; $start >= 0; --$start) {
            $line = '';

            for ($end = $start; $end < $wordCount; ++$end) {
                $candidateLine = $line === '' ? $words[$end] : $line.' '.$words[$end];
                $lineLength = mb_strlen($candidateLine);

                if ($lineLength > $availableWidth && $start !== $end) {
                    break;
                }

                $remainingCost = $costs[$end + 1];

                if ($remainingCost === PHP_INT_MAX) {
                    $line = $candidateLine;

                    continue;
                }

                $candidateCost = $this->lineCost($candidateLine, $availableWidth, $end === $wordCount - 1) + $remainingCost;

                if ($candidateCost < $costs[$start]) {
                    $costs[$start] = $candidateCost;
                    $breaks[$start] = $end + 1;
                }

                $line = $candidateLine;
            }
        }

        $lines = [];
        $index = 0;

        while ($index < $wordCount) {
            $nextIndex = $breaks[$index] ?? min($index + 1, $wordCount);
            $lines[] = implode(' ', array_slice($words, $index, $nextIndex - $index));
            $index = $nextIndex;
        }

        return $lines;
    }

    private function lineCost(string $line, int $availableWidth, bool $isLastLine): int
    {
        $lineLength = mb_strlen($line);
        $slack = $availableWidth - $lineLength;

        if ($isLastLine) {
            $cost = intdiv($slack * $slack, 4);

            if (!str_contains($line, ' ')) {
                return $cost + 10_000;
            }

            if ($lineLength < (int) ($availableWidth * 0.25)) {
                return $cost + 1_000;
            }

            return $cost;
        }

        return $slack * $slack;
    }

    private function isWrapCandidateLine(string $line): bool
    {
        $trimmedLine = mb_trim($line);

        if (in_array($trimmedLine, ['', '/**', '*/'], true)) {
            return false;
        }

        if (preg_match('/^\s*\*\s{2,}\S/', $line) === 1) {
            return false;
        }

        if (!preg_match('/^\s*\*\s?(.*)$/', $line, $matches)) {
            return false;
        }

        $text = mb_trim($matches[1]);

        return $text !== '' && $text[0] !== '@';
    }
}
