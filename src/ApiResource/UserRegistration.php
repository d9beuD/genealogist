<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Dto\RegisteredUserOutput;
use App\Dto\RegisterUserInput;
use App\State\UserRegistrationCollectionProvider;
use App\State\RegisterUserProcessor;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/register',
            output: RegisteredUserOutput::class,
            provider: UserRegistrationCollectionProvider::class,
        ),
        new Post(
            uriTemplate: '/register',
            input: RegisterUserInput::class,
            output: RegisteredUserOutput::class,
            processor: RegisterUserProcessor::class,
        ),
    ],
)]
final class UserRegistration {}
