<?php

declare(strict_types=1);

namespace App\Entity\Trait;

trait FormatEmptyStringTrait
{
    public function formatEmptyString(?string $value): ?string
    {
        return (null !== $value && '' === trim($value)) ? null : $value;
    }
}
