<?php

declare(strict_types=1);

namespace App\Dto;

final readonly class MeOutput
{
    public function __construct(
        public int $id,
        public string $email,
        public string $firstname,
        public string $lastname,
        public array $roles,
    ) {}
}
