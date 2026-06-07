<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\MeOutput;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * @implements ProviderInterface<MeOutput>
 */
final readonly class MeProvider implements ProviderInterface
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private RoleHierarchyInterface $roleHierarchy,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?\App\Dto\MeOutput
    {
        $token = $this->tokenStorage->getToken();

        if (!$token instanceof \Symfony\Component\Security\Core\Authentication\Token\TokenInterface || !$token instanceof TokenInterface) {
            return null;
        }

        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return null;
        }

        if (!method_exists($user, 'getId')) {
            return null;
        }

        $roles = $this->roleHierarchy->getReachableRoleNames($user->getRoles());

        return new MeOutput(
            id: $user->getId() ?? 0,
            email: $user->getEmail() ?? '',
            firstname: $user->getFirstname() ?? '',
            lastname: $user->getLastname() ?? '',
            roles: $roles,
        );
    }
}
