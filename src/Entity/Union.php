<?php

namespace App\Entity;

use App\Entity\Trait\FormatEmptyStringTrait;
use App\Repository\UnionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UnionRepository::class)]
#[ORM\Table(name: '`union`')]
class Union
{
    use FormatEmptyStringTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToMany(targetEntity: Person::class, inversedBy: 'unions')]
    private Collection $people;

    #[ORM\OneToMany(targetEntity: Person::class, mappedBy: 'parentUnion')]
    private Collection $children;

    #[ORM\Column]
    private ?bool $married = false;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeInterface $startsAt = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $place = null;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $dayUnsure = false;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $monthUnsure = false;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $yearUnsure = false;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $endsAt = null;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $endDayUnsure = null;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $endMonthUnsure = null;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $endYearUnsure = null;

    public function __construct()
    {
        $this->people = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Person>
     */
    public function getPeople(): Collection
    {
        return $this->people;
    }

    public function addPerson(Person $person): static
    {
        if (!$this->people->contains($person)) {
            $this->people->add($person);
        }

        return $this;
    }

    public function removePerson(Person $person): static
    {
        $this->people->removeElement($person);

        return $this;
    }

    /**
     * @return Collection<int, Person>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(Person $person): static
    {
        if (!$this->children->contains($person)) {
            $this->children->add($person);
            $person->setParentUnion($this);
        }

        return $this;
    }

    public function removeChild(Person $person): static
    {
        // set the owning side to null (unless already changed)
        if ($this->children->removeElement($person) && $person->getParentUnion() === $this) {
            $person->setParentUnion(null);
        }

        return $this;
    }

    public function isMarried(): ?bool
    {
        return $this->married;
    }

    public function setMarried(bool $married): static
    {
        $this->married = $married;

        return $this;
    }

    public function getStartsAt(): ?\DateTimeImmutable
    {
        return $this->startsAt;
    }

    public function setStartsAt(?\DateTimeImmutable $startsAt): static
    {
        $this->startsAt = $startsAt;

        return $this;
    }

    public function getPlace(): ?string
    {
        return $this->formatEmptyString($this->place);
    }

    public function setPlace(?string $place): static
    {
        $this->place = $place;

        return $this;
    }

    public function hasChildren(): bool
    {
        return !$this->children->isEmpty();
    }

    public function isDayUnsure(): ?bool
    {
        return $this->dayUnsure;
    }

    public function setDayUnsure(bool $dayUnsure): static
    {
        $this->dayUnsure = $dayUnsure;

        return $this;
    }

    public function isMonthUnsure(): ?bool
    {
        return $this->monthUnsure;
    }

    public function setMonthUnsure(bool $monthUnsure): static
    {
        $this->monthUnsure = $monthUnsure;

        return $this;
    }

    public function isYearUnsure(): ?bool
    {
        return $this->yearUnsure;
    }

    public function setYearUnsure(bool $yearUnsure): static
    {
        $this->yearUnsure = $yearUnsure;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->formatEmptyString($this->description);
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPartner(Person $person): ?Person
    {
        foreach ($this->people as $partner) {
            if ($partner !== $person) {
                return $partner;
            }
        }

        return null;
    }

    public function getEndsAt(): ?\DateTimeImmutable
    {
        return $this->endsAt;
    }

    public function setEndsAt(?\DateTimeImmutable $endsAt): static
    {
        $this->endsAt = $endsAt;

        return $this;
    }

    public function isEndDayUnsure(): ?bool
    {
        return $this->endDayUnsure;
    }

    public function setEndDayUnsure(bool $endDayUnsure): static
    {
        $this->endDayUnsure = $endDayUnsure;

        return $this;
    }

    public function isEndMonthUnsure(): ?bool
    {
        return $this->endMonthUnsure;
    }

    public function setEndMonthUnsure(bool $endMonthUnsure): static
    {
        $this->endMonthUnsure = $endMonthUnsure;

        return $this;
    }

    public function isEndYearUnsure(): ?bool
    {
        return $this->endYearUnsure;
    }

    public function setEndYearUnsure(bool $endYearUnsure): static
    {
        $this->endYearUnsure = $endYearUnsure;

        return $this;
    }
}
