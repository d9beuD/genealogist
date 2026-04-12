<?php

namespace App\Service;

use App\Entity\FavoriteMember;
use App\Entity\Person;
use App\Entity\User;
use App\Repository\FavoriteMemberRepository;
use Doctrine\ORM\EntityManagerInterface;

class FavoriteMemberManager
{
    public function __construct(
        private readonly FavoriteMemberRepository $favoriteMemberRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    protected function isFavorite(Person $person, User $user): bool
    {
        $favoriteMember = $this->favoriteMemberRepository->findOneBy([
            'user' => $user,
            'person' => $person,
        ]);

        return $favoriteMember instanceof FavoriteMember;
    }

    public function favorite(Person $person, User $user): void
    {
        $person->getTree();

        if ($this->isFavorite($person, $user)) {
            return;
        }

        $favoriteMember = new FavoriteMember();
        $favoriteMember
            ->setPerson($person)
            ->setUser($user)
        ;

        $this->entityManager->persist($favoriteMember);
        $this->entityManager->flush();
    }

    public function unfavorite(Person $person, User $user): void
    {
        $favoriteMember = $this->favoriteMemberRepository->findOneBy([
            'user' => $user,
            'person' => $person,
        ]);

        if (!$favoriteMember instanceof FavoriteMember) {
            return;
        }

        $this->entityManager->remove($favoriteMember);
        $this->entityManager->flush();
    }
}
