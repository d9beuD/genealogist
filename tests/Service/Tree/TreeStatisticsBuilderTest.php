<?php

declare(strict_types=1);

namespace App\Tests\Service\Tree;

use App\Entity\Person;
use App\Entity\Tree;
use App\Repository\PersonRepository;
use App\Service\Tree\TreeStatisticsBuilder;
use PHPUnit\Framework\TestCase;

final class TreeStatisticsBuilderTest extends TestCase
{
    public function testItBuildsGenderSummariesAndYearlyCharts(): void
    {
        $tree = new Tree();
        $members = [
            $this->createPerson('Ada', 'Alpha', Person::FEMALE, '1900-01-01', '1970-01-01', true),
            $this->createPerson('Beth', 'Beta', Person::FEMALE, '1910-01-01', null, false),
            $this->createPerson('Carl', 'Gamma', Person::MALE, '1890-05-04', '1950-02-01', true),
            $this->createPerson('Dylan', 'Delta', Person::MALE, '1925-06-01', null, false, true),
            $this->createPerson('Evan', 'Epsilon', Person::OTHER, '1930-01-01', '2000-01-01', true),
        ];

        $builder = new TreeStatisticsBuilder($this->createRepositoryStub($tree, $members));
        $statistics = $builder->build($tree);

        self::assertSame(5, $statistics['members_count']);
        self::assertSame('GAMMA Carl', $statistics['oldest_birth']['person']->getFullName());
        self::assertSame('1950-02-01', $statistics['oldest_death']['date']->format('Y-m-d'));

        self::assertNotNull($statistics['women_age_summary']);
        self::assertSame('ALPHA Ada', $statistics['women_age_summary']['min']['person']->getFullName());
        self::assertSame(70, $statistics['women_age_summary']['min']['age']);
        self::assertSame('BETA Beth', $statistics['women_age_summary']['max']['person']->getFullName());
        self::assertSame(2, $statistics['women_age_summary']['average']['count']);

        self::assertNotNull($statistics['men_age_summary']);
        self::assertSame('GAMMA Carl', $statistics['men_age_summary']['min']['person']->getFullName());
        self::assertSame('DELTA Dylan', $statistics['men_age_summary']['max']['person']->getFullName());
        self::assertTrue($statistics['men_age_summary']['max']['approximate']);
        self::assertTrue($statistics['men_age_summary']['average']['approximate']);

        self::assertNotNull($statistics['unknown_gender_age_summary']);
        self::assertSame('EPSILON Evan', $statistics['unknown_gender_age_summary']['min']['person']->getFullName());

        self::assertSame('1890', $statistics['births_by_year']['labels'][0]);
        self::assertSame('1930', $statistics['births_by_year']['labels'][array_key_last($statistics['births_by_year']['labels'])]);
        self::assertCount(41, $statistics['births_by_year']['labels']);
        self::assertSame(1, $statistics['births_by_year']['data'][0]);
        self::assertSame(0, $statistics['births_by_year']['data'][1]);
        self::assertSame(1, $statistics['births_by_year']['data'][10]);
        self::assertSame(0, $statistics['births_by_year']['data'][11]);
        self::assertSame(1, $statistics['births_by_year']['data'][35]);
        self::assertSame(1, $statistics['births_by_year']['data'][40]);

        self::assertSame('1950', $statistics['deaths_by_year']['labels'][0]);
        self::assertSame('2000', $statistics['deaths_by_year']['labels'][array_key_last($statistics['deaths_by_year']['labels'])]);
        self::assertCount(51, $statistics['deaths_by_year']['labels']);
        self::assertSame(1, $statistics['deaths_by_year']['data'][0]);
        self::assertSame(0, $statistics['deaths_by_year']['data'][1]);
        self::assertSame(1, $statistics['deaths_by_year']['data'][20]);
        self::assertSame(0, $statistics['deaths_by_year']['data'][21]);
        self::assertSame(1, $statistics['deaths_by_year']['data'][50]);
    }

    public function testItHidesUnknownGenderSummaryWhenNoUsableUnknownAgeExists(): void
    {
        $tree = new Tree();
        $members = [
            $this->createPerson('Ada', 'Alpha', Person::FEMALE, '1900-01-01', '1970-01-01', true),
            $this->createPerson('Noel', 'Unknown', Person::OTHER, null, null, false),
        ];

        $builder = new TreeStatisticsBuilder($this->createRepositoryStub($tree, $members));
        $statistics = $builder->build($tree);

        self::assertNull($statistics['unknown_gender_age_summary']);
    }

    public function testItReturnsEmptyDateDatasetsWhenNoDatesAreAvailable(): void
    {
        $tree = new Tree();
        $members = [
            $this->createPerson('Noel', 'Unknown', Person::OTHER, null, null, false),
        ];

        $builder = new TreeStatisticsBuilder($this->createRepositoryStub($tree, $members));
        $statistics = $builder->build($tree);

        self::assertNull($statistics['oldest_birth']);
        self::assertNull($statistics['oldest_death']);
        self::assertSame([], $statistics['births_by_year']['labels']);
        self::assertSame([], $statistics['deaths_by_year']['labels']);
    }

    public function testItBuildsStatisticsFromAnExplicitMemberSubset(): void
    {
        $root = $this->createPerson('Root', 'Selected', Person::MALE, '1950-01-01', null, false);
        $ancestor = $this->createPerson('Older', 'Ancestor', Person::FEMALE, '1900-01-01', '1980-01-01', true);
        $excluded = $this->createPerson('Hidden', 'Branch', Person::FEMALE, '1800-01-01', '1870-01-01', true);

        $builder = new TreeStatisticsBuilder($this->createStub(PersonRepository::class));
        $statistics = $builder->buildFromMembers([$root, $ancestor]);

        self::assertSame(2, $statistics['members_count']);
        self::assertSame('ANCESTOR Older', $statistics['oldest_birth']['person']->getFullName());
        self::assertSame('1980-01-01', $statistics['oldest_death']['date']->format('Y-m-d'));
        self::assertNotSame('BRANCH Hidden', $statistics['oldest_birth']['person']->getFullName());
        self::assertSame(['1900', '1901', '1902'], array_slice($statistics['births_by_year']['labels'], 0, 3));
        self::assertSame('1950', $statistics['births_by_year']['labels'][array_key_last($statistics['births_by_year']['labels'])]);
        self::assertSame(1, $statistics['births_by_year']['data'][0]);
        self::assertSame(0, $statistics['births_by_year']['data'][1]);
    }

    /**
     * @param array<int, Person> $members
     */
    private function createRepositoryStub(Tree $tree, array $members): PersonRepository
    {
        $repository = $this->createMock(PersonRepository::class);
        $repository
            ->expects(self::once())
            ->method('findByTreeForStatistics')
            ->with($tree)
            ->willReturn($members)
        ;

        return $repository;
    }

    private function createPerson(
        string $firstname,
        string $lastname,
        ?int $gender,
        ?string $birth,
        ?string $death,
        bool $dead,
        bool $approximateBirth = false,
    ): Person {
        $person = (new Person())
            ->setFirstname($firstname)
            ->setLastname($lastname)
            ->setGender($gender)
            ->setDead($dead)
        ;

        if (null !== $birth) {
            $person->setBirth(new \DateTimeImmutable($birth));
        }

        if (null !== $death) {
            $person->setDeath(new \DateTimeImmutable($death));
        }

        if ($approximateBirth) {
            $person->setBirthYearUnsure(true);
        }

        return $person;
    }
}
