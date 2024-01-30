<?php

namespace App\Security\Voter;

use App\Entity\Union;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class UnionVoter extends Voter
{
    public const EDIT = 'edit';
    public const VIEW = 'view';
    public const DELETE = 'delete';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE])
            && $subject instanceof \App\Entity\Union;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var Union */
        $union = $subject;

        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($union, $user);
                break;

            case self::VIEW:
                return $this->canView($union, $user);
                break;

            case self::DELETE:
                return $this->canDelete($union, $user);
                break;
        }
    }

    private function isOwner(Union $union, User $user): bool
    {
        return $user === $union->getPeople()[0]->getTree()->getOwner();
    }

    private function canEdit(Union $union, User $user): bool
    {
        return $this->isOwner($union, $user);
    }

    private function canView(Union $union, User $user): bool
    {
        return $this->isOwner($union, $user);
    }

    private function canDelete(Union $union, User $user): bool
    {
        return $this->isOwner($union, $user);
    }
}
