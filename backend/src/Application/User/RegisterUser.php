<?php

declare(strict_types=1);

namespace App\Application\User;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class RegisterUser
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $userPasswordHasher,
    ) {}

    public function __invoke(RegisterUserCommand $registerUserCommand): User
    {
        $email = strtolower(trim($registerUserCommand->email));

        if ($this->userRepository->existsByEmail($email)) {
            throw new UserAlreadyExists($email);
        }

        $user = new User()
            ->setEmail($email)
            ->setFirstname(trim($registerUserCommand->firstname))
            ->setLastname(trim($registerUserCommand->lastname))
            ->setIsVerified(false)
        ;

        $user->setPassword($this->userPasswordHasher->hashPassword($user, $registerUserCommand->plainPassword));

        $this->userRepository->save($user);

        return $user;
    }
}
