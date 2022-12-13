<?php

namespace App\Entity;

use App\Repository\PersonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

#[ORM\Entity(repositoryClass: PersonRepository::class)]
class Person
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $firstname = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $lastname = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $birthName = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Context([DateTimeNormalizer::FORMAT_KEY => \DateTimeInterface::ISO8601])]
    private ?\DateTimeInterface $birthDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Context([DateTimeNormalizer::FORMAT_KEY => \DateTimeInterface::ISO8601])]
    private ?\DateTimeInterface $deathDate = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $picture = null;

    #[ORM\ManyToOne(inversedBy: 'members')]
    #[ORM\JoinColumn(nullable: false)]
    #[Ignore]
    private ?Tree $tree = null;

    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'children')]
    #[MaxDepth(1)]
    private Collection $parents;

    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'parents')]
    #[MaxDepth(1)]
    private Collection $children;

    #[ORM\Column]
    private bool $isBirthDateKnown = true;

    #[ORM\Column]
    private bool $isDeathDateKnown = true;

    #[ORM\Column]
    private bool $isBirthDateCertain = true;

    #[ORM\Column]
    private bool $isDeathDateCertain = true;

    #[ORM\Column(type: Types::JSON)]
    private array $importantDates = [];

    #[ORM\Column(nullable: true)]
    private ?bool $gender = null;

    public function __construct()
    {
        $this->parents = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getBirthName(): ?string
    {
        return $this->birthName;
    }

    public function setBirthName(?string $birthName): self
    {
        $this->birthName = $birthName;

        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(?\DateTimeInterface $birthDate): self
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getDeathDate(): ?\DateTimeInterface
    {
        return $this->deathDate;
    }

    public function setDeathDate(?\DateTimeInterface $deathDate): self
    {
        $this->deathDate = $deathDate;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): self
    {
        $this->picture = $picture;

        return $this;
    }

    public function getTree(): ?Tree
    {
        return $this->tree;
    }

    public function setTree(?Tree $tree): self
    {
        $this->tree = $tree;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getParents(): Collection
    {
        return $this->parents;
    }

    public function addParent(self $parent): self
    {
        if (!$this->parents->contains($parent)) {
            $this->parents->add($parent);
        }

        return $this;
    }

    public function removeParent(self $parent): self
    {
        $this->parents->removeElement($parent);

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(self $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->addParent($this);
        }

        return $this;
    }

    public function removeChild(self $child): self
    {
        if ($this->children->removeElement($child)) {
            $child->removeParent($this);
        }

        return $this;
    }

    public function isIsBirthDateKnown(): ?bool
    {
        return $this->isBirthDateKnown;
    }

    public function setIsBirthDateKnown(bool $isBirthDateKnown): self
    {
        $this->isBirthDateKnown = $isBirthDateKnown;

        return $this;
    }

    public function isIsDeathDateKnown(): ?bool
    {
        return $this->isDeathDateKnown;
    }

    public function setIsDeathDateKnown(bool $isDeathDateKnown): self
    {
        $this->isDeathDateKnown = $isDeathDateKnown;

        return $this;
    }

    public function isIsBirthDateCertain(): ?bool
    {
        return $this->isBirthDateCertain;
    }

    public function setIsBirthDateCertain(bool $isBirthDateCertain): self
    {
        $this->isBirthDateCertain = $isBirthDateCertain;

        return $this;
    }

    public function isIsDeathDateCertain(): ?bool
    {
        return $this->isDeathDateCertain;
    }

    public function setIsDeathDateCertain(bool $isDeathDateCertain): self
    {
        $this->isDeathDateCertain = $isDeathDateCertain;

        return $this;
    }

    public function getImportantDates(): array
    {
        return $this->importantDates;
    }

    public function setImportantDates(array $importantDates): self
    {
        $this->importantDates = $importantDates;

        return $this;
    }

    public function isGender(): ?bool
    {
        return $this->gender;
    }

    public function setGender(?bool $gender): self
    {
        $this->gender = $gender;

        return $this;
    }
}
