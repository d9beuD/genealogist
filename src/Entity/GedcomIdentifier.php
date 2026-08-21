<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'gedcom_identifier')]
#[ORM\UniqueConstraint(name: 'uniq_gedcom_identifier', columns: ['import_id', 'record_type', 'external_id'])]
class GedcomIdentifier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?Person $person = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?Union $union = null;

    public function __construct(
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private ?GedcomImport $import,
        #[ORM\Column(length: 16)]
        private string $recordType,
        #[ORM\Column(length: 255)]
        private string $externalId
    ) {}

    public function getRecordType(): string
    {
        return $this->recordType;
    }

    public function getImport(): ?GedcomImport
    {
        return $this->import;
    }

    public function getExternalId(): string
    {
        return $this->externalId;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(Person $person): static
    {
        $this->person = $person;
        return $this;
    }

    public function getUnion(): ?Union
    {
        return $this->union;
    }

    public function setUnion(Union $union): static
    {
        $this->union = $union;
        return $this;
    }
}
