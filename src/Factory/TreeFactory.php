<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Tree;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

final class TreeFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Tree::class;
    }

    protected function defaults(): array
    {
        return [
            'user' => UserFactory::new(),
            'name' => mb_substr(self::faker()->lastName() . ' Family', 0, 30),
            'createdAt' => new \DateTimeImmutable('-1 year'),
        ];
    }
}
