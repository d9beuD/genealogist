<?php

namespace D9beud\FamilyTree\ORM\Entity;

use D9beud\FamilyTree\ORM\Repository\UserRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;

#[Entity(repositoryClass: UserRepository::class)]
class User
{
    public const EMAIL_LENGTH = 139;
    public const NAME_LEGNTH = 30;

    #[Id]
    #[Column]
    #[GeneratedValue]
    protected int $id;

    #[Column(length: self::NAME_LEGNTH)]
    protected string $firstname;

    #[Column(length: self::NAME_LEGNTH)]
    protected string $lastname;

    #[Column(length: self::EMAIL_LENGTH)]
    protected string $email;

    #[Column]
    protected string $password;

    #[Column]
    protected string $otp;
}
