<?php

namespace App\Security\Voter;

use App\Entity\Tree;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class TreeVoter extends Voter
{
    public const EDIT = 'edit';
    public const VIEW = 'view';
    public const DELETE = 'delete';
    public const ADD_MEMBER = 'add_member';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE, self::ADD_MEMBER])
            && $subject instanceof \App\Entity\Tree;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var Tree */
        $tree = $subject;

        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($tree, $user);
                break;

            case self::VIEW:
                return $this->canView($tree, $user);
                break;

            case self::DELETE:
                return $this->canDelete($tree, $user);
                break;

            case self::ADD_MEMBER:
                return $this->canAddMember($tree, $user);
                break;
        }

        return false;
    }

    private function isOwner(Tree $tree, User $user): bool
    {
        return $user === $tree->getOwner();
    }

    private function canEdit(Tree $tree, User $user): bool
    {
        return $this->isOwner($tree, $user);
    }

    private function canView(Tree $tree, User $user): bool
    {
        return $this->isOwner($tree, $user);
    }

    private function canDelete(Tree $tree, User $user): bool
    {
        return $this->isOwner($tree, $user);
    }

    private function canAddMember(Tree $tree, User $user): bool
    {
        return $this->isOwner($tree, $user);
    }
}
