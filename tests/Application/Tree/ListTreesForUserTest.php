<?php

declare(strict_types=1);

namespace App\Tests\Application\Tree;

use App\Application\Tree\ListTreesForUser;
use App\Entity\Tree;
use App\Entity\User;
use App\Repository\TreeRepository;
use PHPUnit\Framework\TestCase;

final class ListTreesForUserTest extends TestCase
{
    public function testItReturnsOwnedTreesAsOutputDtos(): void
    {
        $user = new User();
        $createdAt = new \DateTimeImmutable('2026-06-06 10:00:00');
        $tree = new Tree()
            ->setName('Family tree')
            ->setCreatedAt($createdAt)
            ->setUser($user)
        ;
        $this->setEntityId($tree, 42);

        $repository = $this->createMock(TreeRepository::class);
        $repository->expects(self::once())
            ->method('findOwnedBy')
            ->with($user)
            ->willReturn([$tree])
        ;

        $outputs = (new ListTreesForUser($repository))($user);

        self::assertCount(1, $outputs);
        self::assertSame(42, $outputs[0]->id);
        self::assertSame('Family tree', $outputs[0]->name);
        self::assertSame($createdAt, $outputs[0]->createdAt);
    }

    private function setEntityId(Tree $tree, int $id): void
    {
        $property = new \ReflectionProperty(Tree::class, 'id');
        $property->setValue($tree, $id);
    }
}
