<?php

declare(strict_types=1);

namespace App\Tests\Service\Tree;

use App\Entity\Person;
use App\Entity\Tree;
use App\Entity\Union;
use App\Repository\PersonRepository;
use App\Service\Tree\PersonAncestorBranchMemberCollector;
use PHPUnit\Framework\TestCase;

final class PersonAncestorBranchMemberCollectorTest extends TestCase
{
    public function testItCollectsSelectedPersonAndUniqueAncestors(): void
    {
        $tree = new Tree();
        $root = $this->createPerson($tree, 'Root', 'Person');
        $mother = $this->createPerson($tree, 'Mother', 'Parent');
        $father = $this->createPerson($tree, 'Father', 'Parent');
        $grandMother = $this->createPerson($tree, 'Grand', 'Mother');
        $sharedAncestor = $this->createPerson($tree, 'Shared', 'Ancestor');

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

        $repository = $this->createMock(PersonRepository::class);
        $repository
            ->expects(self::once())
            ->method('findByTreeForStatisticsGraph')
            ->with($root->getTree())
            ->willReturn([$root, $mother, $father, $grandMother, $sharedAncestor])
        ;

        $members = (new PersonAncestorBranchMemberCollector($repository))->collect($root);

        self::assertSame([
            'ANCESTOR Shared',
            'MOTHER Grand',
            'PARENT Father',
            'PARENT Mother',
            'PERSON Root',
        ], array_map(static fn (Person $person): string => $person->getFullName(), $members));
    }

    private function createPerson(Tree $tree, string $firstname, string $lastname): Person
    {
        return (new Person())
            ->setFirstname($firstname)
            ->setLastname($lastname)
            ->setTree($tree)
        ;
    }
}
