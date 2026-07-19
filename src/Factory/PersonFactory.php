<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Person;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

final class PersonFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Person::class;
    }

    protected function defaults(): array
    {
        return [
            'tree' => TreeFactory::new(),
            'firstname' => self::faker()->firstName(),
            'lastname' => self::faker()->lastName(),
            'gender' => self::faker()->randomElement([Person::FEMALE, Person::MALE, Person::OTHER]),
            'birth' => \DateTimeImmutable::createFromMutable(self::faker()->dateTimeBetween('-90 years', '-18 years')),
            'birthPlace' => mb_substr(self::faker()->city() . ', ' . self::faker()->country(), 0, 60),
            'bio' => self::faker()->paragraph(),
            'dead' => false,
        ];
    }

    public function male(): self
    {
        return $this->with([
            'gender' => Person::MALE,
            'firstname' => self::faker()->firstNameMale(),
        ]);
    }

    public function female(): self
    {
        return $this->with([
            'gender' => Person::FEMALE,
            'firstname' => self::faker()->firstNameFemale(),
        ]);
    }

    public function living(): self
    {
        return $this->with([
            'dead' => false,
            'death' => null,
            'deathPlace' => null,
        ]);
    }

    public function deceased(): self
    {
        return $this->with([
            'dead' => true,
            'death' => new \DateTimeImmutable('-10 years'),
            'deathPlace' => mb_substr(self::faker()->city() . ', ' . self::faker()->country(), 0, 60),
        ]);
    }

    public function unsureBirth(): self
    {
        return $this->with([
            'birthDayUnsure' => true,
            'birthMonthUnsure' => true,
            'birthYearUnsure' => true,
        ]);
    }
}
