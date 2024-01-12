<?php

namespace App\Entity;

use App\Repository\PersonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PersonRepository::class)]
class Person
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private ?string $firstname = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $lastname = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $birth = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $death = null;

    #[ORM\Column]
    private ?bool $birthDaySure = null;

    #[ORM\Column]
    private ?bool $birthMonthSure = null;

    #[ORM\Column]
    private ?bool $birthYearSure = null;

    #[ORM\Column]
    private ?bool $deathDaySure = null;

    #[ORM\Column]
    private ?bool $deathMonthSure = null;

    #[ORM\Column]
    private ?bool $deathYearSure = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $portrait = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $bio = '';

    #[ORM\ManyToMany(targetEntity: Union::class, mappedBy: 'people')]
    private Collection $unions;

    #[ORM\ManyToOne(inversedBy: 'children')]
    private ?Union $parentUnion = null;

    #[ORM\ManyToOne(inversedBy: 'members')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tree $tree = null;

    public function __construct()
    {
        $this->unions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getBirth(): ?\DateTimeInterface
    {
        return $this->birth;
    }

    public function setBirth(?\DateTimeInterface $birth): static
    {
        $this->birth = $birth;

        return $this;
    }

    public function getDeath(): ?\DateTimeInterface
    {
        return $this->death;
    }

    public function setDeath(?\DateTimeInterface $death): static
    {
        $this->death = $death;

        return $this;
    }

    public function isBirthDaySure(): ?bool
    {
        return $this->birthDaySure;
    }

    public function setBirthDaySure(bool $birthDaySure): static
    {
        $this->birthDaySure = $birthDaySure;

        return $this;
    }

    public function isBirthMonthSure(): ?bool
    {
        return $this->birthMonthSure;
    }

    public function setBirthMonthSure(bool $birthMonthSure): static
    {
        $this->birthMonthSure = $birthMonthSure;

        return $this;
    }

    public function isBirthYearSure(): ?bool
    {
        return $this->birthYearSure;
    }

    public function setBirthYearSure(bool $birthYearSure): static
    {
        $this->birthYearSure = $birthYearSure;

        return $this;
    }

    public function isDeathDaySure(): ?bool
    {
        return $this->deathDaySure;
    }

    public function setDeathDaySure(bool $deathDaySure): static
    {
        $this->deathDaySure = $deathDaySure;

        return $this;
    }

    public function isDeathMonthSure(): ?bool
    {
        return $this->deathMonthSure;
    }

    public function setDeathMonthSure(bool $deathMonthSure): static
    {
        $this->deathMonthSure = $deathMonthSure;

        return $this;
    }

    public function isDeathYearSure(): ?bool
    {
        return $this->deathYearSure;
    }

    public function setDeathYearSure(bool $deathYearSure): static
    {
        $this->deathYearSure = $deathYearSure;

        return $this;
    }

    public function getPortrait(): ?string
    {
        return $this->portrait;
    }

    public function setPortrait(?string $portrait): static
    {
        $this->portrait = $portrait;

        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): static
    {
        $this->bio = $bio;

        return $this;
    }

    /**
     * @return Collection<int, Union>
     */
    public function getUnions(): Collection
    {
        return $this->unions;
    }

    public function addUnion(Union $union): static
    {
        if (!$this->unions->contains($union)) {
            $this->unions->add($union);
            $union->addPerson($this);
        }

        return $this;
    }

    public function removeUnion(Union $union): static
    {
        if ($this->unions->removeElement($union)) {
            $union->removePerson($this);
        }

        return $this;
    }

    public function getParentUnion(): ?Union
    {
        return $this->parentUnion;
    }

    public function setParentUnion(?Union $parentUnion): static
    {
        $this->parentUnion = $parentUnion;

        return $this;
    }

    public function getTree(): ?Tree
    {
        return $this->tree;
    }

    public function setTree(?Tree $tree): static
    {
        $this->tree = $tree;

        return $this;
    }
}
