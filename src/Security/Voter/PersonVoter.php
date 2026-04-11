<?php

namespace App\Security\Voter;

use App\Entity\Person;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class PersonVoter extends Voter
{
    public const EDIT = 'edit';
    public const VIEW = 'view';
    public const DELETE = 'delete';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE])
            && $subject instanceof \App\Entity\Person;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var Person */
        $person = $subject;

        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($person, $user);

            case self::VIEW:
                return $this->canView($person, $user);

            case self::DELETE:
                return $this->canDelete($person, $user);
        }

        return false;
    }

    private function isOwner(Person $person, User $user): bool
    {
        return $user === $person->getTree()->getOwner();
    }

    private function canEdit(Person $person, User $user): bool
    {
        return $this->isOwner($person, $user);
    }

    private function canView(Person $person, User $user): bool
    {
        return $this->isOwner($person, $user);
    }

    private function canDelete(Person $person, User $user): bool
    {
        return $this->isOwner($person, $user);
    }
}
