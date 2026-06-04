<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\Application\User\RegisterUser;
use App\Application\User\RegisterUserCommand;
use App\Application\User\UserAlreadyExists;
use App\Dto\RegisteredUserOutput;
use App\Dto\RegisterUserInput;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements ProcessorInterface<RegisterUserInput, RegisteredUserOutput>
 */
final readonly class RegisterUserProcessor implements ProcessorInterface
{
    public function __construct(
        private RegisterUser $registerUser,
        private TranslatorInterface $translator,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): RegisteredUserOutput
    {
        if (!$data instanceof RegisterUserInput) {
            throw new \InvalidArgumentException(\sprintf('Expected %s.', RegisterUserInput::class));
        }

        try {
            $user = ($this->registerUser)(new RegisterUserCommand(
                email: $data->email,
                plainPassword: $data->plainPassword,
                firstname: $data->firstname,
                lastname: $data->lastname,
            ));
        } catch (UserAlreadyExists $exception) {
            throw new ValidationException(new ConstraintViolationList([
                new ConstraintViolation(
                    message: $this->translator->trans('registration.email.already_used', domain: 'validators'),
                    messageTemplate: 'registration.email.already_used',
                    parameters: [],
                    root: $data,
                    propertyPath: 'email',
                    invalidValue: $data->email,
                    code: null,
                    cause: $exception,
                ),
            ]));
        }

        return new RegisteredUserOutput(
            email: $user->getEmail() ?? '',
            firstname: $user->getFirstname() ?? '',
            lastname: $user->getLastname() ?? '',
        );
    }
}
