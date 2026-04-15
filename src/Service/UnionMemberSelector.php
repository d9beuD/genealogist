<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Person;
use App\Repository\PersonRepository;
use DateTimeImmutable;

final readonly class UnionMemberSelector
{
    public function __construct(
        private PersonRepository $personRepository,
    ) {
    }

    /**
     * @return list<Person>
     */
    public function getSelectablePeople(int $treeId, ?int $personToFilterFromId = null, ?int $excludedPersonId = null): array
    {
        $people = $this->personRepository->findByTreeIdOrderedByName($treeId);
        $personToFilterFrom = $this->findPerson($personToFilterFromId);

        return array_values(array_filter(
            $people,
            fn (Person $person): bool => $this->isSelectablePerson($person, $personToFilterFrom, $excludedPersonId)
        ));
    }

    public function isSelectable(int $treeId, int $candidateId, ?int $personToFilterFromId = null, ?int $excludedPersonId = null): bool
    {
        $candidate = $this->findPerson($candidateId);

        if (!$candidate instanceof Person || $candidate->getTree()->getId() !== $treeId) {
            return false;
        }

        return $this->isSelectablePerson($candidate, $this->findPerson($personToFilterFromId), $excludedPersonId);
    }

    private function findPerson(?int $personId): ?Person
    {
        if (null === $personId) {
            return null;
        }

        $person = $this->personRepository->find($personId);

        return $person instanceof Person ? $person : null;
    }

    private function isSelectablePerson(Person $person, ?Person $personToFilterFrom, ?int $excludedPersonId): bool
    {
        if (null !== $excludedPersonId && $person->getId() === $excludedPersonId) {
            return false;
        }

        if (!$personToFilterFrom instanceof Person) {
            return true;
        }

        if ($person->getId() === $personToFilterFrom->getId()) {
            return false;
        }

        if ($personToFilterFrom->getBirth() instanceof DateTimeImmutable && $person->getDeath() instanceof DateTimeImmutable && $person->getDeath() <= $personToFilterFrom->getBirth()) {
            return false;
        }

        if ($personToFilterFrom->getDeath() instanceof DateTimeImmutable && $person->getBirth() instanceof DateTimeImmutable && $person->getBirth() >= $personToFilterFrom->getDeath()) {
            return false;
        }

        return true;
    }
}
