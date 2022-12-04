<?php

namespace App\Entity;

use App\Repository\CoupleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CoupleRepository::class)]
class Couple
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToMany(targetEntity: Person::class, mappedBy: 'couples')]
    private Collection $people;

    #[ORM\ManyToMany(targetEntity: Person::class, mappedBy: 'parents')]
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

    /**
     * @return Collection<int, Person>
     */
    public function getPeople(): Collection
    {
        return $this->people;
    }

    public function addPerson(Person $person): self
    {
        if (!$this->people->contains($person)) {
            $this->people->add($person);
            $person->addCouple($this);
        }

        return $this;
    }

    public function removePerson(Person $person): self
    {
        if ($this->people->removeElement($person)) {
            $person->removeCouple($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Person>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(Person $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->addParent($this);
        }

        return $this;
    }

    public function removeChild(Person $child): self
    {
        if ($this->children->removeElement($child)) {
            $child->removeParent($this);
        }

        return $this;
    }
}
