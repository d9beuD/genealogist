<?php

namespace App\Service\Tree;

use App\Entity\Person;
use App\Repository\PersonRepository;

class PersonAncestorBranchMemberCollector
{
    public function __construct(
        private readonly PersonRepository $personRepository,
    ) {
    }

    /**
     * @return array<int, Person>
     */
    public function collect(Person $person): array
    {
        $treeMembers = $this->personRepository->findByTreeForStatisticsGraph($person->getTree());
        $indexedMembers = [];

        foreach ($treeMembers as $member) {
            $indexedMembers[$this->getPersonKey($member)] = $member;
        }

        $root = $indexedMembers[$this->getPersonKey($person)] ?? $person;
        $members = [];
        $visited = [];
        $stack = [$root];

        while ([] !== $stack) {
            $current = array_pop($stack);
            $key = $this->getPersonKey($current);

            if (isset($visited[$key])) {
                continue;
            }

            $visited[$key] = true;
            $members[] = $current;

            $parentUnion = $current->getParentUnion();

            if (null === $parentUnion) {
                continue;
            }

            foreach ($parentUnion->getPeople() as $parent) {
                $stack[] = $parent;
            }
        }

        usort($members, static fn (Person $left, Person $right): int => strcmp($left->getFullName(), $right->getFullName()));

        return $members;
    }

    private function getPersonKey(Person $person): string
    {
        return null === $person->getId() ? 'obj-'.spl_object_id($person) : 'id-'.$person->getId();
    }
}
