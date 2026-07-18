<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Union;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

final class UnionFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Union::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'married' => true,
            'startsAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTimeBetween('-70 years', '-20 years')),
            'place' => mb_substr(self::faker()->city() . ', ' . self::faker()->country(), 0, 100),
            'description' => self::faker()->optional()->sentence(),
        ];
    }

    public function withFamily(array $partners = [], array $children = []): self
    {
        return $this->afterInstantiate(static function (Union $union) use ($partners, $children): void {
            foreach ($partners as $person) {
                $person->addUnion($union);
            }
            foreach ($children as $person) {
                $union->addChild($person);
            }
        });
    }
}
