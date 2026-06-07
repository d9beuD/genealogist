<?php

declare(strict_types=1);

namespace App\Application\Tree;

use App\Entity\Tree;
use App\Repository\TreeRepository;

final readonly class CreateTree
{
    public function __construct(
        private TreeRepository $trees,
    ) {}

    public function __invoke(CreateTreeCommand $command): Tree
    {
        $tree = new Tree()
            ->setUser($command->owner)
            ->setName(trim($command->name))
            ->setCreatedAt(new \DateTimeImmutable())
        ;

        $this->trees->save($tree);

        return $tree;
    }
}
