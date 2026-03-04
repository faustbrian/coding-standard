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
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;

use const T_STRING;
use const T_TRAIT;

use function str_ends_with;

/**
 * @author Brian Faust <brian@cline.sh>
 * @version 1.0.0
 */
final class TraitNameFixer extends AbstractFixer
{
    private const string SUFFIX = 'Trait';

    #[Override()]
    public function getName(): string
    {
        return 'Architecture/trait_name_fixer';
    }

    #[Override()]
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Exception classes should have suffix "Trait".',
            [],
        );
    }

    /**
     * @param Tokens<Token> $tokens
     */
    #[Override()]
    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAllTokenKindsFound([T_TRAIT]);
    }

    /**
     * @param Tokens<Token> $tokens
     */
    #[Override()]
    protected function applyFix(SplFileInfo $file, Tokens $tokens): void
    {
        foreach ($tokens as $index => $token) {
            if (!$token->isGivenKind(T_TRAIT)) {
                continue;
            }

            $classNameIndex = $tokens->getNextMeaningfulToken($index);

            if ($classNameIndex === null) {
                continue;
            }

            $classNameToken = $tokens[$classNameIndex]->getContent();

            if (str_ends_with($classNameToken, self::SUFFIX)) {
                continue;
            }

            $tokens[$classNameIndex] = new Token([T_STRING, $classNameToken.self::SUFFIX]);
        }
    }
}
