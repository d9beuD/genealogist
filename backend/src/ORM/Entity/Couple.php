<?php

namespace D9beud\FamilyTree\ORM\Entity;

use D9beud\FamilyTree\ORM\Repository\CoupleRepository;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\OneToMany;

#[Entity(repositoryClass: CoupleRepository::class)]
class Couple
{
    #[Id]
    #[Column]
    #[GeneratedValue]
    protected int $id;

    #[Column]
    protected bool $isActive = true;

    #[Column]
    protected bool $isMarried = false;

    #[Column(nullable: true)]
    protected ?DateTime $togehterSince = null;

    #[ManyToMany(targetEntity: Person::class, inversedBy: 'couples')]
    protected Collection $partners;

    #[OneToMany(targetEntity: Person::class, mappedBy: 'parents')]
    protected Collection $children;
}
