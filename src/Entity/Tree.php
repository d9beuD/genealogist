<?php

namespace App\Entity;

use App\Repository\TreeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TreeRepository::class)]
class Tree
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'trees')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\OneToMany(targetEntity: Person::class, mappedBy: 'tree', orphanRemoval: true)]
    #[ORM\OrderBy(['lastname' => 'ASC', 'firstname' => 'ASC'])]
    private Collection $members;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 30, options: ['default' => 'My family tree'])]
    private ?string $name = null;

    public function __construct()
    {
        $this->members = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, Person>
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(Person $person): static
    {
        if (!$this->members->contains($person)) {
            $this->members->add($person);
            $person->setTree($this);
        }

        return $this;
    }

    public function removeMember(Person $person): static
    {
        // set the owning side to null (unless already changed)
        if ($this->members->removeElement($person) && $person->getTree() === $this) {
            $person->setTree(null);
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }
}
