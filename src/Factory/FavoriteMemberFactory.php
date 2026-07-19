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

    /**
     * @return array<string, \Zenstruck\Foundry\Persistence\PersistentObjectFactory>
     */
    protected function defaults(): array
    {
        return [
            'user' => UserFactory::new(),
            'person' => PersonFactory::new(),
        ];
    }
}
