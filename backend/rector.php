<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector;
use Rector\Config\RectorConfig;
use Rector\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector;
use Rector\Naming\Rector\Class_\RenamePropertyToMatchTypeRector;
use Rector\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector;
use Rector\Naming\Rector\ClassMethod\RenameVariableToMatchNewTypeRector;
use Rector\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector;
use Rector\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withAttributesSets(symfony: true)
    ->withPhpSets(php84: true)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        doctrineCodeQuality: true,
        earlyReturn: true,
        instanceOf: true,
        naming: true,
        privatization: true,
        symfonyCodeQuality: true,
        typeDeclarationDocblocks: true,
        typeDeclarations: true,
    )
    ->withSkip(
        [
            CatchExceptionNameMatchingTypeRector::class,
            RenameForeachValueVariableToMatchExprVariableRector::class,
            RenameForeachValueVariableToMatchMethodCallReturnTypeRector::class,
            RenameParamToMatchTypeRector::class,
            RenamePropertyToMatchTypeRector::class,
            RenameVariableToMatchMethodCallReturnTypeRector::class,
            RenameVariableToMatchNewTypeRector::class,
        ]
    );
