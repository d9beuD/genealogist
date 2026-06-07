<?php

declare(strict_types=1);

namespace App\Application\Tree;

use App\Entity\User;

final readonly class CreateTreeCommand
{
    public function __construct(
        public User $owner,
        public string $name,
    ) {}
}
