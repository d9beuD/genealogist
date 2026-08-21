<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'gedcom_metadata')]
class GedcomMetadata
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

    /** @param array<string, mixed> $payload */
    public function __construct(
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private ?GedcomImport $import,
        #[ORM\Column(length: 16)]
        private string $type,
        #[ORM\Column(length: 255, nullable: true)]
        private ?string $externalId,
        #[ORM\Column(type: Types::JSON)]
        private array $payload
    ) {}

    public function setPerson(?Person $person): static
    {
        $this->person = $person;
        return $this;
    }

    public function setUnion(?Union $union): static
    {
        $this->union = $union;
        return $this;
    }
}
