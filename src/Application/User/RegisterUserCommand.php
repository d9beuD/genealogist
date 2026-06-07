<?php

declare(strict_types=1);

namespace App\Application\User;

final readonly class RegisterUserCommand
{
    public function __construct(
        public string $email,
        public string $plainPassword,
        public string $firstname,
        public string $lastname,
    ) {}
}
