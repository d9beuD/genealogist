<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final readonly class ResourcePermissions
{
    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker,
    ) {}

    /**
     * @return list<string>
     */
    public function getPermissions(object $subject): array
    {
        $permissions = [];

        if ($this->authorizationChecker->isGranted('view', $subject)) {
            $permissions[] = 'view';
        }

        if ($this->authorizationChecker->isGranted('edit', $subject)) {
            $permissions[] = 'edit';
        }

        if ($this->authorizationChecker->isGranted('delete', $subject)) {
            $permissions[] = 'delete';
        }

        if ($this->authorizationChecker->isGranted('add_member', $subject)) {
            $permissions[] = 'add_member';
        }

        return $permissions;
    }
}
