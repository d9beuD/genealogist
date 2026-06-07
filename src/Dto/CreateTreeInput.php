<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateTreeInput
{
    #[Assert\NotBlank(message: 'tree.name.not_blank')]
    #[Assert\Length(max: 30, maxMessage: 'tree.name.too_long')]
    public string $name = '';
}
