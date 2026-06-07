<?php

declare(strict_types=1);

namespace App\Dto;

final readonly class TreeOutput
{
    public function __construct(
        public int $id,
        public string $name,
        public \DateTimeImmutable $createdAt,
    ) {}
}
