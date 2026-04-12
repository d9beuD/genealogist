<?php

declare(strict_types=1);

namespace App\Entity\Trait;

trait FormatEmptyStringTrait
{
    public function formatEmptyString(?string $value): ?string
    {
        return ($value !== null && strlen(trim($value)) === 0) ? null : $value;
    }
}
