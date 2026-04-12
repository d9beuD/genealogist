<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Person;
use PHPUnit\Framework\TestCase;

final class PersonTest extends TestCase
{
    public function testItComputesAgeForLivingPeople(): void
    {
        $person = (new Person())
            ->setBirth(new \DateTimeImmutable('2000-01-15'));

        self::assertSame(
            (new \DateTimeImmutable('2000-01-15'))->diff(new \DateTimeImmutable('today'))->y,
            $person->getAge()
        );
        self::assertFalse($person->isAgeApproximate());
    }

    public function testItComputesAgeAtDeath(): void
    {
        $person = (new Person())
            ->setBirth(new \DateTimeImmutable('1950-06-10'))
            ->setDeath(new \DateTimeImmutable('2000-06-09'))
            ->setDead(true);

        self::assertSame(49, $person->getAge());
        self::assertFalse($person->isAgeApproximate());
    }

    public function testItMarksAgeAsApproximateWhenBirthIsUncertain(): void
    {
        $person = (new Person())
            ->setBirth(new \DateTimeImmutable('1980-03-20'))
            ->setBirthYearUnsure(true);

        self::assertNotNull($person->getAge());
        self::assertTrue($person->isAgeApproximate());
    }

    public function testItMarksAgeAsApproximateWhenDeathIsUncertain(): void
    {
        $person = (new Person())
            ->setBirth(new \DateTimeImmutable('1980-03-20'))
            ->setDeath(new \DateTimeImmutable('2020-03-19'))
            ->setDeathMonthUnsure(true)
            ->setDead(true);

        self::assertSame(39, $person->getAge());
        self::assertTrue($person->isAgeApproximate());
    }

    public function testItReturnsUnknownAgeWithoutBirthDate(): void
    {
        $person = new Person();

        self::assertNull($person->getAge());
        self::assertFalse($person->isAgeApproximate());
    }

    public function testItReturnsUnknownAgeForDeceasedPeopleWithoutDeathDate(): void
    {
        $person = (new Person())
            ->setBirth(new \DateTimeImmutable('1980-03-20'))
            ->setDead(true);

        self::assertNull($person->getAge());
        self::assertFalse($person->isAgeApproximate());
    }
}
