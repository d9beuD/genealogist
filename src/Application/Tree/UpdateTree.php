<?php

declare(strict_types=1);

namespace App\Application\Tree;

use App\Entity\Tree;
use App\Repository\TreeRepository;

final readonly class UpdateTree
{
    public function __construct(
        private TreeRepository $trees,
    ) {}

    public function __invoke(UpdateTreeCommand $command): Tree
    {
        $command->tree->setName(trim($command->name));

        $this->trees->save($command->tree);

        return $command->tree;
    }
}
