<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Application\Tree\ListTreesForUser;
use App\Dto\TreeOutput;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @implements ProviderInterface<list<TreeOutput>>
 */
final readonly class TreesProvider implements ProviderInterface
{
    public function __construct(
        private Security $security,
        private ListTreesForUser $listTreesForUser,
    ) {}

    /**
     * @return list<TreeOutput>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return [];
        }

        return ($this->listTreesForUser)($user);
    }
}
