<?php

declare(strict_types=1);

namespace App\Application\User;

use RuntimeException;

final class UserAlreadyExists extends RuntimeException
{
    public function __construct(string $email)
    {
        parent::__construct(\sprintf('The email "%s" is already registered.', $email));
    }
}
