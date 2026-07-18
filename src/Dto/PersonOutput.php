<?php

declare(strict_types=1);

namespace App\Dto;

final readonly class PersonOutput
{
    public function __construct(
        public int $id,
        public ?string $firstname,
        public ?string $lastname,
        public ?\DateTimeImmutable $birth,
        public ?\DateTimeImmutable $death,
        public bool $birthDayUnsure,
        public bool $birthMonthUnsure,
        public bool $birthYearUnsure,
        public bool $deathDayUnsure,
        public bool $deathMonthUnsure,
        public bool $deathYearUnsure,
        public ?string $portrait,
        public ?string $bio,
        public ?int $gender,
        public bool $dead,
        public ?string $birthName,
        public ?string $otherNames,
        public ?string $birthPlace,
        public ?string $deathPlace,
    ) {}
}
