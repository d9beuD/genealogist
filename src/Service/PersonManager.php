<?php

namespace App\Service;

use App\Entity\Person;
use App\Entity\Tree;
use App\Repository\PersonRepository;

class PersonManager
{
    public function __construct(
        private readonly PersonRepository $personRepository,
    ) {
    }

    /**
     * @return array<string, array<int, Person>>
     */
    public function findGroupedByTree(Tree $tree, ?string $name = null): array
    {
        return $this->groupByFirstLetter($this->personRepository->findByTreeWithFavorites($tree, $name));
    }

    /**
     * @return array<string, array<int, Person>>
     */
    public function findGroupedWithoutOwnUnions(Tree $tree, ?string $name = null): array
    {
        return $this->groupByFirstLetter($this->personRepository->findWithoutOwnUnions($tree, $name));
    }

    /**
     * @return array<string, array<int, Person>>
     */
    public function findGroupedWithoutParentUnion(Tree $tree, ?string $name = null): array
    {
        return $this->groupByFirstLetter($this->personRepository->findWithoutParentUnion($tree, $name));
    }

    /**
     * @param array<int, Person> $members
     *
     * @return array<string, array<int, Person>>
     */
    public function groupByFirstLetter(array $members): array
    {
        usort(
            $members,
            fn (Person $left, Person $right): int => strcmp(
                trim($left->getFullName()),
                trim($right->getFullName())
            )
        );

        return array_reduce($members, function (array $groupedMembers, Person $person): array {
            $firstLetter = strtoupper(mb_substr(trim($person->getFullName()), 0, 1));
            $groupedMembers[$firstLetter][] = $person;

            return $groupedMembers;
        }, []);
    }
}
