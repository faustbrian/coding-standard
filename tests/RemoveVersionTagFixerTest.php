<?php

declare(strict_types=1);

use Cline\CodingStandard\PhpCsFixer\Fixer\RemoveVersionTagFixer;
use PhpCsFixer\Tokenizer\Tokens;

it('removes version tags while keeping other annotations', function (): void {
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

    $fixer = new RemoveVersionTagFixer();

    $tokens = Tokens::fromCode($code);
    $fixer->fix(new SplFileInfo(__FILE__), $tokens);

    $result = $tokens->generateCode();

    expect($result)->not->toContain('@version');
    expect($result)->toContain(' * @author Brian Faust <brian@cline.sh>');
});

it('removes docblocks that only contain a version tag', function (): void {
    $code = <<<'PHP'
<?php
/**
 * @version 1.0.0
 */
final class Example
{
}
PHP;

    $fixer = new RemoveVersionTagFixer();

    $tokens = Tokens::fromCode($code);
    $fixer->fix(new SplFileInfo(__FILE__), $tokens);

    $result = $tokens->generateCode();

    expect($result)->not->toContain('/**');
    expect($result)->not->toContain('@version');
    expect($result)->toContain("<?php\nfinal class Example");
});
