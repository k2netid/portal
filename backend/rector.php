<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__.'/app',
        __DIR__.'/Modules',
    ]);

    // Exclude tests
    $rectorConfig->skip([
        __DIR__.'/Modules/*/tests',
        __DIR__.'/tests',
    ]);

    // define sets of rules
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_82,
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        SetList::TYPE_DECLARATION, // This will fix the missing return types and property types
    ]);

    // Register a single rule
    $rectorConfig->rule(AddVoidReturnTypeWhereNoReturnRector::class);
};
