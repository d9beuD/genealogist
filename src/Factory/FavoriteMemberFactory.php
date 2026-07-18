<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\FavoriteMember;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

final class FavoriteMemberFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return FavoriteMember::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'user' => UserFactory::new(),
            'person' => PersonFactory::new(),
        ];
    }
}
