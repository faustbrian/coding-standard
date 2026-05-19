<?php

declare(strict_types=1);

it('registers the php 8.5 pipe operator rectors', function (): void {
    $config = file_get_contents(__DIR__.'/../rector.php');

    expect($config)->not->toBeFalse();
    expect($config)->toContain('NestedFuncCallsToPipeOperatorRector::class');
    expect($config)->toContain('SequentialAssignmentsToPipeOperatorRector::class');
    expect($config)->toContain('->withRules([');
});
