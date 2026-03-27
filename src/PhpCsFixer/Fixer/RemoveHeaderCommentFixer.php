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

use const T_DECLARE;
use const T_DOC_COMMENT;
use const T_OPEN_TAG;
use const T_WHITESPACE;

/**
 * Removes the leading file header comment after the opening tag or strict types
 * declaration.
 */
final class RemoveHeaderCommentFixer extends AbstractFixer
{
    #[Override()]
    public function getName(): string
    {
        return 'Architecture/remove_header_comment_fixer';
    }

    #[Override()]
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Remove the file-level header comment after the opening tag or declare statement.',
            [
                new CodeSample(
                    '<?php declare(strict_types=1);

/**
 * Copyright (C) Example
 */

namespace App;
',
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
        return $this->findHeaderCommentIndex($tokens) !== null;
    }

    /**
     * @param Tokens<Token> $tokens
     */
    #[Override()]
    protected function applyFix(SplFileInfo $file, Tokens $tokens): void
    {
        $headerIndex = $this->findHeaderCommentIndex($tokens);

        if ($headerIndex === null) {
            return;
        }

        $tokens->clearAt($headerIndex);

        $nextIndex = $headerIndex + 1;

        if (!isset($tokens[$nextIndex]) || !$tokens[$nextIndex]->isGivenKind(T_WHITESPACE)) {
            return;
        }

        $tokens->clearAt($nextIndex);
    }

    /**
     * @param Tokens<Token> $tokens
     */
    private function findHeaderCommentIndex(Tokens $tokens): ?int
    {
        $index = 0;

        if (!isset($tokens[$index]) || !$tokens[$index]->isGivenKind(T_OPEN_TAG)) {
            return null;
        }

        $index = $tokens->getNextNonWhitespace($index);

        if ($index === null) {
            return null;
        }

        if ($tokens[$index]->isGivenKind(T_DECLARE)) {
            while (isset($tokens[$index]) && !$tokens[$index]->equals(';')) {
                ++$index;
            }

            $index = $tokens->getNextNonWhitespace($index);

            if ($index === null) {
                return null;
            }
        }

        if (!$tokens[$index]->isGivenKind(T_DOC_COMMENT)) {
            return null;
        }

        return $index;
    }
}
