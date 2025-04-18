<?php

namespace App\Entity;

use App\Entity\Trait\FormatEmptyStringTrait;
use App\Repository\PersonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PersonRepository::class)]
class Person
{
    use FormatEmptyStringTrait;

    public const FEMALE = 0;
    public const MALE = 1;
    public const OTHER = 2;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30, options: ['default' => ''], nullable: true)]
    private ?string $firstname = null;

    #[ORM\Column(length: 30, options:['default' => ''], nullable: true)]
    private ?string $lastname = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $birth = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $death = null;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $birthDayUnsure = null;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $birthMonthUnsure = null;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $birthYearUnsure = null;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $deathDayUnsure = null;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $deathMonthUnsure = null;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $deathYearUnsure = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $portrait = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $bio = '';

    #[ORM\ManyToMany(targetEntity: Union::class, mappedBy: 'people')]
    private Collection $unions;

    #[ORM\ManyToOne(inversedBy: 'children')]
    private ?Union $parentUnion = null;

    #[ORM\ManyToOne(inversedBy: 'members', fetch: 'EAGER')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tree $tree = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    #[Assert\Choice(choices: [self::FEMALE, self::MALE, self::OTHER])]
    private ?int $gender = null;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $dead = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $birthName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $otherNames = null;

    #[ORM\Column(length: 60, nullable: true)]
    private ?string $birthPlace = null;

    #[ORM\Column(length: 60, nullable: true)]
    private ?string $deathPlace = null;

    #[ORM\OneToMany(mappedBy: 'person', targetEntity: Source::class, orphanRemoval: true)]
    private Collection $sources;

    public function __construct()
    {
        $this->unions = new ArrayCollection();
        $this->sources = new ArrayCollection();

        $this->birthDayUnsure = false;
        $this->birthMonthUnsure = false;
        $this->birthYearUnsure = false;

        $this->deathDayUnsure = false;
        $this->deathMonthUnsure = false;
        $this->deathYearUnsure = false;

        $this->dead = false;
    }

    public function __toString(): string
    {
        return $this->getFullName();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->formatEmptyString($this->firstname);
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->formatEmptyString($this->lastname);
    }

    public function setLastname(?string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getDefaultLastname(): string
    {
        return $this->getBirthName() ?? $this->getLastname() ?? '';
    }

    public function getFullName(): string
    {
        return trim(mb_strtoupper($this->getDefaultLastname()) . ' ' . $this->firstname);
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

    public function isBirthDayUnsure(): ?bool
    {
        return $this->birthDayUnsure;
    }

    public function setBirthDayUnsure(bool $birthDayUnsure): static
    {
        $this->birthDayUnsure = $birthDayUnsure;

        return $this;
    }

    public function isBirthMonthUnsure(): ?bool
    {
        return $this->birthMonthUnsure;
    }

    public function setBirthMonthUnsure(bool $birthMonthUnsure): static
    {
        $this->birthMonthUnsure = $birthMonthUnsure;

        return $this;
    }

    public function isBirthYearUnsure(): ?bool
    {
        return $this->birthYearUnsure;
    }

    public function setBirthYearUnsure(bool $birthYearUnsure): static
    {
        $this->birthYearUnsure = $birthYearUnsure;

        return $this;
    }

    public function isDeathDayUnsure(): ?bool
    {
        return $this->deathDayUnsure;
    }

    public function setDeathDayUnsure(bool $deathDayUnsure): static
    {
        $this->deathDayUnsure = $deathDayUnsure;

        return $this;
    }

    public function isDeathMonthUnsure(): ?bool
    {
        return $this->deathMonthUnsure;
    }

    public function setDeathMonthUnsure(bool $deathMonthUnsure): static
    {
        $this->deathMonthUnsure = $deathMonthUnsure;

        return $this;
    }

    public function isDeathYearUnsure(): ?bool
    {
        return $this->deathYearUnsure;
    }

    public function setDeathYearUnsure(bool $deathYearUnsure): static
    {
        $this->deathYearUnsure = $deathYearUnsure;

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
        return $this->formatEmptyString($this->bio);
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

    public function getGender(): ?int
    {
        return $this->gender;
    }

    public function setGender(?int $gender): static
    {
        $this->gender = $gender;

        return $this;
    }

    public function isDead(): ?bool
    {
        return $this->dead;
    }

    public function setDead(bool $dead): static
    {
        $this->dead = $dead;

        return $this;
    }

    public function getBirthName(): ?string
    {
        return $this->formatEmptyString($this->birthName);
    }

    public function setBirthName(?string $birthName): static
    {
        $this->birthName = $birthName;

        return $this;
    }

    public function getOtherNames(): ?string
    {
        return $this->otherNames;
    }

    public function setOtherNames(?string $otherNames): static
    {
        $this->otherNames = $otherNames;

        return $this;
    }

    public function getBirthPlace(): ?string
    {
        return $this->formatEmptyString($this->birthPlace);
    }

    public function setBirthPlace(?string $birthPlace): static
    {
        $this->birthPlace = $birthPlace;

        return $this;
    }

    public function getDeathPlace(): ?string
    {
        return $this->formatEmptyString($this->deathPlace);
    }

    public function setDeathPlace(?string $deathPlace): static
    {
        $this->deathPlace = $deathPlace;

        return $this;
    }

    public function hasChildren(): bool
    {
        return array_reduce(
            $this->unions->toArray(),
            fn ($carry, Union $union) => $carry || $union->hasChildren(),
            false
        );
    }

    public function getChildren(): Collection
    {
        $children = new ArrayCollection();
        foreach ($this->getUnions() as $union) {
            foreach ($union->getChildren() as $child) {
                $children->add($child);
            }
        }

        return $children;
    }

    /**
     * @return Collection<int, Source>
     */
    public function getSources(): Collection
    {
        return $this->sources;
    }

    public function addSource(Source $source): static
    {
        if (!$this->sources->contains($source)) {
            $this->sources->add($source);
            $source->setPerson($this);
        }

        return $this;
    }

    public function removeSource(Source $source): static
    {
        if ($this->sources->removeElement($source)) {
            // set the owning side to null (unless already changed)
            if ($source->getPerson() === $this) {
                $source->setPerson(null);
            }
        }

        return $this;
    }
}
