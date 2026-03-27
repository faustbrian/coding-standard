<?php

declare(strict_types=1);

use Cline\CodingStandard\PhpCsFixer\Fixer\RemoveHeaderCommentFixer;
use PhpCsFixer\Tokenizer\Tokens;

it('removes the file header comment after strict types', function (): void {
    $code = <<<'PHP'
<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests;
PHP;

    $fixer = new RemoveHeaderCommentFixer();

    $tokens = Tokens::fromCode($code);
    $fixer->fix(new SplFileInfo(__FILE__), $tokens);

    $result = $tokens->generateCode();

    expect($result)->not->toContain('Copyright (C) Brian Faust');
    expect($result)->toContain("<?php declare(strict_types=1);\n\nnamespace Tests;");
});

it('leaves later docblocks untouched', function (): void {
    $code = <<<'PHP'
<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests;

/**
 * Class docblock.
 */
final class Example
{
}
PHP;

    $fixer = new RemoveHeaderCommentFixer();

    $tokens = Tokens::fromCode($code);
    $fixer->fix(new SplFileInfo(__FILE__), $tokens);

    $result = $tokens->generateCode();

    expect($result)->not->toContain('Copyright (C) Brian Faust');
    expect($result)->toContain("/**\n * Class docblock.\n */\nfinal class Example");
});
