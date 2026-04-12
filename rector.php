<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

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
    );
