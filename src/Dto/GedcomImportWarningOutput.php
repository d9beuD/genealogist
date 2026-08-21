<?php

declare(strict_types=1);

namespace App\Dto;

final readonly class GedcomImportWarningOutput
{
    public function __construct(
        public string $code,
        public ?int $line,
        public ?string $tag,
        public string $message,
    ) {}
}
