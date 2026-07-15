<?php

declare(strict_types=1);

use Cline\CodingStandard\EasyCodingStandard\Factory;
use Cline\CodingStandard\PhpCsFixer\CopyrightHeader;
use Cline\CodingStandard\PhpCsFixer\Fixer\ImportFqcnInPropertyFixer;
use Cline\CodingStandard\PhpCsFixer\Fixer\PsalmImmutableOnReadonlyClassFixer;
use Cline\CodingStandard\PhpCsFixer\Preset\Standard;
use PhpCsFixer\Fixer\ClassNotation\FinalClassFixer;
use PhpCsFixer\Fixer\FunctionNotation\MultilinePromotedPropertiesFixer;
use Symplify\CodingStandard\Fixer\Spacing\StandaloneLinePromotedPropertyFixer;
use Symplify\EasyCodingStandard\Configuration\ECSConfigBuilder;

it('restores default header rules when a copyright header is provided', function (): void {
    $closure = Factory::create(
        paths: [__DIR__],
        copyrightHeader: new CopyrightHeader('Brian Faust'),
    );

    $variables = (new ReflectionFunction($closure))->getStaticVariables();

    expect($variables['resolvedRules'])->toMatchArray([
        'header_comment' => [
            'comment_type' => 'PHPDoc',
            'header' => (new CopyrightHeader('Brian Faust'))->render(),
            'location' => 'after_declare_strict',
            'separate' => 'both',
        ],
        'Architecture/remove_header_comment_fixer' => false,
    ]);
});

it('lets explicit rules override the default copyright header behavior', function (): void {
    $closure = Factory::create(
        paths: [__DIR__],
        rules: [
            'header_comment' => false,
            'Architecture/remove_header_comment_fixer' => true,
        ],
        copyrightHeader: new CopyrightHeader('Brian Faust'),
    );

    $variables = (new ReflectionFunction($closure))->getStaticVariables();

    expect($variables['resolvedRules']['header_comment'])->toBeFalse();
    expect($variables['resolvedRules']['Architecture/remove_header_comment_fixer'])->toBeTrue();
});

it('builds a fluent ecs config builder with the same defaults', function (): void {
    $builder = Factory::configure(
        paths: [__DIR__],
        skip: [
            FinalClassFixer::class => [__FILE__],
        ],
        copyrightHeader: new CopyrightHeader('Brian Faust'),
    );

    expect($builder)->toBeInstanceOf(ECSConfigBuilder::class);

    $reflection = new ReflectionClass($builder);

    expect($reflection->getProperty('paths')->getValue($builder))->toBe([__DIR__]);
    expect($reflection->getProperty('parallel')->getValue($builder))->toBeTrue();
    expect($reflection->getProperty('skip')->getValue($builder))->toHaveKey(FinalClassFixer::class);
    expect($reflection->getProperty('skip')->getValue($builder))->toHaveKey(ImportFqcnInPropertyFixer::class);
    expect($reflection->getProperty('rules')->getValue($builder))
        ->toContain(MultilinePromotedPropertiesFixer::class);
    expect($reflection->getProperty('rules')->getValue($builder))
        ->toContain(StandaloneLinePromotedPropertyFixer::class);
    expect($reflection->getProperty('rulesWithConfiguration')->getValue($builder))->not->toBeEmpty();
});

it('avoids deprecated nullable default rule configuration', function (): void {
    $rules = (new Standard())->rules();

    expect($rules['nullable_type_declaration_for_default_null_value'])->toBeTrue();
});

it('keeps semantic immutable annotations opt in', function (): void {
    $defaultBuilder = Factory::configure(paths: [__DIR__]);
    $optedInBuilder = Factory::configure(
        paths: [__DIR__],
        rules: [
            'Architecture/psalm_immutable_on_readonly_class_fixer' => true,
        ],
    );

    $defaultReflection = new ReflectionClass($defaultBuilder);
    $optedInReflection = new ReflectionClass($optedInBuilder);

    expect($defaultReflection->getProperty('rules')->getValue($defaultBuilder))
        ->not->toContain(PsalmImmutableOnReadonlyClassFixer::class)
        ->and($optedInReflection->getProperty('rules')->getValue($optedInBuilder))
        ->toContain(PsalmImmutableOnReadonlyClassFixer::class);
});
