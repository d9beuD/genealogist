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

    #[ORM\OneToMany(mappedBy: 'parentUnion', targetEntity: Person::class)]
    private Collection $children;

    #[ORM\Column]
    private ?bool $married = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $weddingDate = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $weddingPlace = null;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $dayUnsure = null;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $monthUnsure = null;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $yearUnsure = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

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

    public function addChild(Person $child): static
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParentUnion($this);
        }

        return $this;
    }

    public function removeChild(Person $child): static
    {
        if ($this->children->removeElement($child)) {
            // set the owning side to null (unless already changed)
            if ($child->getParentUnion() === $this) {
                $child->setParentUnion(null);
            }
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

    public function getWeddingDate(): ?\DateTimeInterface
    {
        return $this->weddingDate;
    }

    public function setWeddingDate(?\DateTimeInterface $weddingDate): static
    {
        $this->weddingDate = $weddingDate;

        return $this;
    }

    public function getWeddingPlace(): ?string
    {
        return $this->formatEmptyString($this->weddingPlace);
    }

    public function setWeddingPlace(?string $weddingPlace): static
    {
        $this->weddingPlace = $weddingPlace;

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
}
