<?php

declare(strict_types=1);

namespace App\Application\Tree;

use App\Entity\Tree;

final readonly class UpdateTreeCommand
{
    public function __construct(
        public Tree $tree,
        public string $name,
    ) {}
}
