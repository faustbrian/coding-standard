<?php

declare(strict_types=1);

use Cline\CodingStandard\EasyCodingStandard\Factory;
use Cline\CodingStandard\PhpCsFixer\CopyrightHeader;
use Cline\CodingStandard\PhpCsFixer\Fixer\ImportFqcnInPropertyFixer;
use PhpCsFixer\Fixer\ClassNotation\FinalClassFixer;
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
    expect($reflection->getProperty('rulesWithConfiguration')->getValue($builder))->not->toBeEmpty();
});
