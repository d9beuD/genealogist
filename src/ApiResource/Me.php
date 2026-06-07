<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Dto\MeOutput;
use App\State\MeProvider;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/me',
            output: MeOutput::class,
            provider: MeProvider::class,
        ),
    ],
)]
final class Me {}
