<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class RegisterUserInput
{
    #[Assert\NotBlank(message: 'registration.email.not_blank')]
    #[Assert\Email(message: 'registration.email.invalid')]
    public string $email = '';

    #[Assert\NotBlank(message: 'registration.firstname.not_blank')]
    #[Assert\Length(max: 30, maxMessage: 'registration.firstname.too_long')]
    public string $firstname = '';

    #[Assert\NotBlank(message: 'registration.lastname.not_blank')]
    #[Assert\Length(max: 30, maxMessage: 'registration.lastname.too_long')]
    public string $lastname = '';

    #[Assert\NotBlank(message: 'registration.password.not_blank')]
    #[Assert\Length(
        min: 8,
        max: 4096,
        minMessage: 'registration.password.too_short',
        maxMessage: 'registration.password.too_long',
    )]
    public string $plainPassword = '';
}
