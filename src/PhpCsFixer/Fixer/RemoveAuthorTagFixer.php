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

use const T_DOC_COMMENT;
use const T_WHITESPACE;

use function mb_ltrim;
use function mb_trim;
use function preg_match;
use function preg_replace;

final class RemoveAuthorTagFixer extends AbstractFixer
{
    #[Override()]
    public function getName(): string
    {
        return 'Architecture/remove_author_tag_fixer';
    }

    #[Override()]
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Remove all @author tags from PHPDoc blocks.',
            [
                new CodeSample(
                    '<?php
/**
 * @author Jane Doe <jane@example.com>
 */
class Example
{
}',
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
        foreach ($tokens as $token) {
            if ($token->isGivenKind(T_DOC_COMMENT) && preg_match('/@author\b/', $token->getContent()) === 1) {
                return true;
            }
        }

        return false;
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

            if (preg_match('/@author\b/', $content) !== 1) {
                continue;
            }

            $updatedContent = preg_replace('/^\h*\*\h*@author\b[^\n]*\n?/m', '', $content);

            if ($updatedContent === null) {
                continue;
            }

            if ($this->isEmptyDocBlock($updatedContent)) {
                $tokens->clearAt($index);

                $nextIndex = $index + 1;

                if (isset($tokens[$nextIndex]) && $tokens[$nextIndex]->isGivenKind(T_WHITESPACE)) {
                    $content = mb_ltrim($tokens[$nextIndex]->getContent(), "\n");

                    if ($content === '') {
                        $tokens->clearAt($nextIndex);
                    } else {
                        $tokens[$nextIndex] = new Token([T_WHITESPACE, $content]);
                    }
                }

                continue;
            }

            $tokens[$index] = new Token([T_DOC_COMMENT, $this->normalizeDocBlock($updatedContent)]);
        }
    }

    private function isEmptyDocBlock(string $content): bool
    {
        $body = preg_replace('/^\h*\/\*\*|\*\/\h*$/m', '', $content);

        if ($body === null) {
            return false;
        }

        $body = preg_replace('/^\h*\*\h?/m', '', $body);

        if ($body === null) {
            return false;
        }

        return mb_trim($body) === '';
    }

    private function normalizeDocBlock(string $content): string
    {
        $content = preg_replace("/\n{3,}/", "\n\n", $content);

        return $content ?? mb_trim('');
    }
}
