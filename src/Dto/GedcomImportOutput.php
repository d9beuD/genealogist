<?php

declare(strict_types=1);

namespace App\Dto;

final readonly class GedcomImportOutput
{
    /** @param list<GedcomImportWarningOutput> $warnings */
    public function __construct(
        public TreeOutput $tree,
        public string $format,
        public array $created,
        public array $updated,
        public array $warnings,
    ) {}
}
