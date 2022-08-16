<?php

namespace D9beud\FamilyTree\ORM\Entity;

use D9beud\FamilyTree\ORM\Repository\PersonRepository;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;

#[Entity(repositoryClass: PersonRepository::class)]
class Person
{
    public const NAME_LEGNTH = 30;

    #[Id]
    #[Column]
    #[GeneratedValue]
    protected int $id;

    #[Column(length: self::NAME_LEGNTH)]
    protected string $firstname;

    #[Column(length: self::NAME_LEGNTH)]
    protected string $lastname;

    #[Column]
    protected DateTime $birthdate;

    #[Column]
    protected DateTime $deathdate;

    #[OneToOne(targetEntity: Person::class, inversedBy: 'children')]
    #[OneToMany(targetEntity: Couple::class, mappedBy: 'partners')]
    protected Collection $couples;

    #[ManyToOne(inversedBy: 'children')]
    protected Couple $parents;
}
