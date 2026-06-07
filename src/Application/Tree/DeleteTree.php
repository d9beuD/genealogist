<?php

declare(strict_types=1);

namespace App\Application\Tree;

use App\Repository\TreeRepository;

final readonly class DeleteTree
{
    public function __construct(
        private TreeRepository $trees,
    ) {}

    public function __invoke(DeleteTreeCommand $command): void
    {
        $this->trees->remove($command->tree);
    }
}
