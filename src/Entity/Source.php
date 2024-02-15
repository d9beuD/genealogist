<?php

namespace App\Entity;

use App\Repository\SourceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SourceRepository::class)]
class Source
{
    public const CERT_BIRTH = 1;
    public const CERT_BAPTISM = 2;
    public const CERT_MARRIAGE = 3;
    public const CERT_DEATH = 4;
    public const CERT_MILITARY = 5;
    public const CERT_OTHER = 6;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Assert\Choice(choices: [
        self::CERT_BIRTH,
        self::CERT_BAPTISM,
        self::CERT_MARRIAGE,
        self::CERT_DEATH,
        self::CERT_MILITARY,
        self::CERT_OTHER
    ])]
    private ?int $type = null;

    #[ORM\Column(length: 2048)]
    private ?string $url = null;

    #[ORM\ManyToOne(inversedBy: 'sources')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Person $person = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function getTypes(): array
    {
        return [
            self::CERT_BIRTH => 'form.choice.certificate_birth',
            self::CERT_BAPTISM => 'form.choice.certificate_baptism',
            self::CERT_MARRIAGE => 'form.choice.certificate_marriage',
            self::CERT_DEATH => 'form.choice.certificate_death',
            self::CERT_MILITARY => 'form.choice.certificate_military',
            self::CERT_OTHER => 'form.choice.other',
        ];
    }

    public function setType(int $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): static
    {
        $this->person = $person;

        return $this;
    }
}
