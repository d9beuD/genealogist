<?php

namespace App\Entity;

use App\Repository\UnionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UnionRepository::class)]
#[ORM\Table(name: '`union`')]
class Union
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $maried = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToMany(targetEntity: Person::class, inversedBy: 'unions')]
    private Collection $people;

    #[ORM\OneToMany(mappedBy: 'parentUnion', targetEntity: Person::class)]
    private Collection $children;

    public function __construct()
    {
        $this->people = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isMaried(): ?bool
    {
        return $this->maried;
    }

    public function setMaried(bool $maried): static
    {
        $this->maried = $maried;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
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
}
