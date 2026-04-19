<?php

namespace App\Service\Tree;

use App\Entity\Person;
use App\Entity\Tree;
use App\Repository\PersonRepository;

class TreeStatisticsBuilder
{
    public function __construct(
        private readonly PersonRepository $personRepository,
    ) {
    }

    /**
     * @return array{
     *     members_count: int,
     *     oldest_birth: ?array{person: Person, date: \DateTimeInterface},
     *     oldest_death: ?array{person: Person, date: \DateTimeInterface},
     *     women_age_summary: ?array{
     *         min: array{person: Person, age: int, approximate: bool},
     *         max: array{person: Person, age: int, approximate: bool},
     *         average: array{age: float, approximate: bool, count: int}
     *     },
     *     men_age_summary: ?array{
     *         min: array{person: Person, age: int, approximate: bool},
     *         max: array{person: Person, age: int, approximate: bool},
     *         average: array{age: float, approximate: bool, count: int}
     *     },
     *     unknown_gender_age_summary: ?array{
     *         min: array{person: Person, age: int, approximate: bool},
     *         max: array{person: Person, age: int, approximate: bool},
     *         average: array{age: float, approximate: bool, count: int}
     *     },
     *     births_by_year: array{labels: list<string>, data: list<int>},
     *     deaths_by_year: array{labels: list<string>, data: list<int>}
     * }
     */
    public function build(Tree $tree): array
    {
        $members = $this->personRepository->findByTreeForStatistics($tree);

        return $this->buildFromMembers($members);
    }

    /**
     * @param array<int, Person> $members
     *
     * @return array{
     *     members_count: int,
     *     oldest_birth: ?array{person: Person, date: \DateTimeInterface},
     *     oldest_death: ?array{person: Person, date: \DateTimeInterface},
     *     women_age_summary: ?array{
     *         min: array{person: Person, age: int, approximate: bool},
     *         max: array{person: Person, age: int, approximate: bool},
     *         average: array{age: float, approximate: bool, count: int}
     *     },
     *     men_age_summary: ?array{
     *         min: array{person: Person, age: int, approximate: bool},
     *         max: array{person: Person, age: int, approximate: bool},
     *         average: array{age: float, approximate: bool, count: int}
     *     },
     *     unknown_gender_age_summary: ?array{
     *         min: array{person: Person, age: int, approximate: bool},
     *         max: array{person: Person, age: int, approximate: bool},
     *         average: array{age: float, approximate: bool, count: int}
     *     },
     *     births_by_year: array{labels: list<string>, data: list<int>},
     *     deaths_by_year: array{labels: list<string>, data: list<int>}
     * }
     */
    public function buildFromMembers(array $members): array
    {
        return [
            'members_count' => count($members),
            'oldest_birth' => $this->findOldestDateRecord($members, static fn (Person $person): ?\DateTimeInterface => $person->getBirth()),
            'oldest_death' => $this->findOldestDateRecord($members, static fn (Person $person): ?\DateTimeInterface => $person->getDeath()),
            'women_age_summary' => $this->buildAgeSummary($members, static fn (Person $person): bool => Person::FEMALE === $person->getGender()),
            'men_age_summary' => $this->buildAgeSummary($members, static fn (Person $person): bool => Person::MALE === $person->getGender()),
            'unknown_gender_age_summary' => $this->buildAgeSummary($members, static fn (Person $person): bool => !in_array($person->getGender(), [Person::FEMALE, Person::MALE], true)),
            'births_by_year' => $this->buildYearDataset($members, static fn (Person $person): ?\DateTimeInterface => $person->getBirth()),
            'deaths_by_year' => $this->buildYearDataset($members, static fn (Person $person): ?\DateTimeInterface => $person->getDeath()),
        ];
    }

    /**
     * @param array<int, Person>                    $members
     * @param callable(Person): ?\DateTimeInterface $dateAccessor
     *
     * @return ?array{person: Person, date: \DateTimeInterface}
     */
    private function findOldestDateRecord(array $members, callable $dateAccessor): ?array
    {
        $oldestRecord = null;

        foreach ($members as $member) {
            $date = $dateAccessor($member);

            if (!$date instanceof \DateTimeInterface) {
                continue;
            }

            if (null === $oldestRecord || $date < $oldestRecord['date']) {
                $oldestRecord = [
                    'person' => $member,
                    'date' => $date,
                ];
            }
        }

        return $oldestRecord;
    }

    /**
     * @param array<int, Person>     $members
     * @param callable(Person): bool $filter
     *
     * @return ?array{
     *     min: array{person: Person, age: int, approximate: bool},
     *     max: array{person: Person, age: int, approximate: bool},
     *     average: array{age: float, approximate: bool, count: int}
     * }
     */
    private function buildAgeSummary(array $members, callable $filter): ?array
    {
        $ageRecords = [];

        foreach ($members as $member) {
            if (!$filter($member)) {
                continue;
            }

            $age = $member->getAge();

            if (null === $age) {
                continue;
            }

            $ageRecords[] = [
                'person' => $member,
                'age' => $age,
                'approximate' => $member->isAgeApproximate(),
            ];
        }

        if ([] === $ageRecords) {
            return null;
        }

        usort(
            $ageRecords,
            static fn (array $left, array $right): int => $left['age'] <=> $right['age'] ?: strcmp($left['person']->getFullName(), $right['person']->getFullName())
        );

        $ageValues = array_column($ageRecords, 'age');

        return [
            'min' => $ageRecords[0],
            'max' => $ageRecords[array_key_last($ageRecords)],
            'average' => [
                'age' => round(array_sum($ageValues) / count($ageValues), 1),
                'approximate' => array_reduce(
                    $ageRecords,
                    static fn (bool $carry, array $record): bool => $carry || $record['approximate'],
                    false,
                ),
                'count' => count($ageRecords),
            ],
        ];
    }

    /**
     * @param array<int, Person>                    $members
     * @param callable(Person): ?\DateTimeInterface $dateAccessor
     *
     * @return array{labels: list<string>, data: list<int>}
     */
    private function buildYearDataset(array $members, callable $dateAccessor): array
    {
        $countsByYear = [];
        $minYear = null;
        $maxYear = null;

        foreach ($members as $member) {
            $date = $dateAccessor($member);

            if (!$date instanceof \DateTimeInterface) {
                continue;
            }

            $year = (int) $date->format('Y');
            $countsByYear[$year] = ($countsByYear[$year] ?? 0) + 1;
            $minYear = null === $minYear ? $year : min($minYear, $year);
            $maxYear = null === $maxYear ? $year : max($maxYear, $year);
        }

        if (null === $minYear || null === $maxYear) {
            return [
                'labels' => [],
                'data' => [],
            ];
        }

        for ($year = $minYear; $year <= $maxYear; ++$year) {
            $countsByYear[$year] ??= 0;
        }

        ksort($countsByYear);

        return [
            'labels' => array_map(strval(...), array_keys($countsByYear)),
            'data' => array_values($countsByYear),
        ];
    }
}
