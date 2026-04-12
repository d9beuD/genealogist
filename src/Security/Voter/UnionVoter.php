<?php

namespace App\Security\Voter;

use App\Entity\Union;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class UnionVoter extends Voter
{
    public const EDIT = 'edit';

    public const VIEW = 'view';

    public const DELETE = 'delete';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE], true)
            && $subject instanceof Union;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var Union */
        $union = $subject;

        return match ($attribute) {
            self::EDIT => $this->canEdit($union, $user),
            self::VIEW => $this->canView($union, $user),
            self::DELETE => $this->canDelete($union, $user),
            default => false,
        };
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
