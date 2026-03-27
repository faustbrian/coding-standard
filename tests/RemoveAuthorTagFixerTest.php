<?php

declare(strict_types=1);

use Cline\CodingStandard\PhpCsFixer\Fixer\RemoveAuthorTagFixer;
use PhpCsFixer\Tokenizer\Tokens;

it('removes author tags while keeping other annotations', function (): void {
    $code = <<<'PHP'
<?php
/**
 * Example summary.
 *
 * @author Brian Faust <brian@cline.sh>
 * @version 1.0.0
 */
final class Example
{
}
PHP;

    $fixer = new RemoveAuthorTagFixer();

    $tokens = Tokens::fromCode($code);
    $fixer->fix(new SplFileInfo(__FILE__), $tokens);

    $result = $tokens->generateCode();

    expect($result)->not->toContain('@author');
    expect($result)->toContain(' * @version 1.0.0');
});

it('removes docblocks that only contain an author tag', function (): void {
    $code = <<<'PHP'
<?php
/**
 * @author Brian Faust <brian@cline.sh>
 */
final class Example
{
}
PHP;

    $fixer = new RemoveAuthorTagFixer();

    $tokens = Tokens::fromCode($code);
    $fixer->fix(new SplFileInfo(__FILE__), $tokens);

    $result = $tokens->generateCode();

    expect($result)->not->toContain('/**');
    expect($result)->not->toContain('@author');
    expect($result)->toContain("<?php\nfinal class Example");
});
