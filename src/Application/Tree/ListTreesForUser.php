<?php

declare(strict_types=1);

namespace App\Application\Tree;

use App\Dto\TreeOutput;
use App\Entity\User;
use App\Repository\TreeRepository;

final readonly class ListTreesForUser
{
    public function __construct(
        private TreeRepository $trees,
    ) {}

    /**
     * @return list<TreeOutput>
     */
    public function __invoke(User $user): array
    {
        return array_map(
            static fn($tree): TreeOutput => new TreeOutput(
                id: $tree->getId() ?? 0,
                name: $tree->getName() ?? '',
                createdAt: $tree->getCreatedAt() ?? new \DateTimeImmutable('@0'),
            ),
            $this->trees->findOwnedBy($user),
        );
    }
}
