<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\User;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

final class UserFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return User::class;
    }

    protected function defaults(): array
    {
        return [
            'email' => self::faker()->unique()->safeEmail(),
            'password' => password_hash('password', PASSWORD_BCRYPT),
            'firstname' => self::faker()->firstName(),
            'lastname' => self::faker()->lastName(),
            'isVerified' => true,
        ];
    }

    public function verified(): self
    {
        return $this->with(['isVerified' => true]);
    }

    public function unverified(): self
    {
        return $this->with(['isVerified' => false]);
    }
}
