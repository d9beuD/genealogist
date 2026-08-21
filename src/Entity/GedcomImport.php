<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'gedcom_import')]
#[ORM\UniqueConstraint(name: 'uniq_gedcom_import_tree_filename', columns: ['tree_id', 'filename'])]
class GedcomImport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Tree $tree = null;

    #[ORM\Column]
    private \DateTimeImmutable $importedAt;

    public function __construct(#[ORM\Column(length: 255)]
        private string $filename, #[ORM\Column(length: 16)]
        private string $format)
    {
        $this->importedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTree(): ?Tree
    {
        return $this->tree;
    }

    public function setTree(Tree $tree): static
    {
        $this->tree = $tree;
        return $this;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function setFormat(string $format): static
    {
        $this->format = $format;
        $this->importedAt = new \DateTimeImmutable();
        return $this;
    }
}
