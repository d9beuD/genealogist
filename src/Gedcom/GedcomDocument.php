<?php

declare(strict_types=1);

namespace App\Gedcom;

final readonly class GedcomDocument
{
    /**
     * @param list<array<string, mixed>> $records
     * @param list<array{name: string, content: string}> $media
     * @param list<array{code: string, line: int|null, tag: string|null, message: string}> $warnings
     */
    public function __construct(
        public string $format,
        public array $records,
        public array $media,
        public array $warnings,
    ) {}
}
