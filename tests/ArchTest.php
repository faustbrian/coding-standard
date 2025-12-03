<?php

declare(strict_types=1);

arch('source files use strict types')
    ->expect('Cline\CodingStandard')
    ->toUseStrictTypes();

arch('source files have correct namespace')
    ->expect('Cline\CodingStandard')
    ->toBeClasses()
    ->ignoring('Cline\CodingStandard\PhpCsFixer\Preset\PresetInterface');

arch('interfaces have correct suffix')
    ->expect('Cline\CodingStandard')
    ->interfaces()
    ->toHaveSuffix('Interface');

arch('fixers extend abstract fixer')
    ->expect('Cline\CodingStandard\PhpCsFixer\Fixer')
    ->classes()
    ->toExtend('PhpCsFixer\AbstractFixer')
    ->ignoring('Cline\CodingStandard\PhpCsFixer\Fixer\AbstractNameFixer');
