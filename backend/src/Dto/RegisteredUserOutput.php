<?php

declare(strict_types=1);

namespace App\Dto;

final readonly class RegisteredUserOutput
{
    public function __construct(
        public string $email,
        public string $firstname,
        public string $lastname,
    ) {}
}
