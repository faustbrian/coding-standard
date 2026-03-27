<?php

declare(strict_types=1);

use Cline\CodingStandard\PhpCsFixer\Fixer\PhpdocLineLengthFixer;
use PhpCsFixer\Tokenizer\Tokens;

it('wraps long phpdoc prose lines', function (): void {
    $code = <<<'PHP'
<?php
/**
 * Execute the deletion workflow for the pricing route distance row.
 *
 * Validates the request payload, resolves the target aggregate, applies
 * idempotency and lifecycle-state rules, and returns a lifecycle result that
 * distinguishes success, replay, and failure outcomes.
 *
 * This fixer focuses on `new` expressions and avoids changes
 * when doing so.
 *
 * @author Brian Faust <brian@cline.sh>
 * @version 1.0.0
 *
 * @param mixed $input Request data for the deletion workflow
 *
 * @return mixed Structured outcome describing success, replay, or failure
 */
final class Example
{
}
PHP;

    $fixer = new PhpdocLineLengthFixer();

    $tokens = Tokens::fromCode($code);
    $fixer->fix(new SplFileInfo(__FILE__), $tokens);

    $result = $tokens->generateCode();

    expect($result)->not->toBe($code);
    expect($result)->not->toContain(" * This fixer focuses on `new` expressions and avoids changes\n * when doing so.");
    expect($result)->toContain(' * This fixer focuses on `new` expressions and avoids changes when doing so.');
    expect($result)->toContain(' * @author Brian Faust <brian@cline.sh>');
    expect($result)->toContain(' * @version 1.0.0');
    expect($result)->toContain('* @param mixed $input Request data for the deletion workflow');

    foreach (explode("\n", $result) as $line) {
        $trimmedLine = trim($line);

        if ($trimmedLine === '' || $trimmedLine === '/**' || $trimmedLine === '*/') {
            continue;
        }

        if (str_contains($line, '@')) {
            continue;
        }

        expect(strlen($line))->toBeLessThanOrEqual(80);
    }
});
