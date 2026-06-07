<?php

declare(strict_types=1);

namespace App\Application\User;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class RegisterUser
{
    public function __construct(
        private UserRepository $users,
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function __invoke(RegisterUserCommand $command): User
    {
        $email = strtolower(trim($command->email));

        if ($this->users->existsByEmail($email)) {
            throw new UserAlreadyExists($email);
        }

        $user = new User()
            ->setEmail($email)
            ->setFirstname(trim($command->firstname))
            ->setLastname(trim($command->lastname))
            ->setIsVerified(false)
        ;

        $user->setPassword($this->passwordHasher->hashPassword($user, $command->plainPassword));

        $this->users->save($user);

        return $user;
    }
}
