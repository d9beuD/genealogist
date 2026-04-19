<?php

declare(strict_types=1);

namespace App\Tests\Service\Tree;

use App\Entity\Person;
use App\Entity\Union;
use App\Service\Tree\PersonAncestorBranchMemberCollector;
use PHPUnit\Framework\TestCase;

final class PersonAncestorBranchMemberCollectorTest extends TestCase
{
    public function testItCollectsSelectedPersonAndUniqueAncestors(): void
    {
        $root = $this->createPerson('Root', 'Person');
        $mother = $this->createPerson('Mother', 'Parent');
        $father = $this->createPerson('Father', 'Parent');
        $grandMother = $this->createPerson('Grand', 'Mother');
        $sharedAncestor = $this->createPerson('Shared', 'Ancestor');

        $rootParents = (new Union())
            ->addPerson($mother)
            ->addPerson($father)
        ;
        $root->setParentUnion($rootParents);

        $motherParents = (new Union())
            ->addPerson($grandMother)
            ->addPerson($sharedAncestor)
        ;
        $mother->setParentUnion($motherParents);

        $fatherParents = (new Union())
            ->addPerson($sharedAncestor)
        ;
        $father->setParentUnion($fatherParents);

        $members = (new PersonAncestorBranchMemberCollector())->collect($root);

        self::assertSame([
            'ANCESTOR Shared',
            'MOTHER Grand',
            'PARENT Father',
            'PARENT Mother',
            'PERSON Root',
        ], array_map(static fn (Person $person): string => $person->getFullName(), $members));
    }

    private function createPerson(string $firstname, string $lastname): Person
    {
        return (new Person())
            ->setFirstname($firstname)
            ->setLastname($lastname)
        ;
    }
}
