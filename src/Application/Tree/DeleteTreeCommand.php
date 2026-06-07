<?php

declare(strict_types=1);

namespace App\Application\Tree;

use App\Entity\Tree;

final readonly class DeleteTreeCommand
{
    public function __construct(
        public Tree $tree,
    ) {}
}
