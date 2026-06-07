<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Dto\CreateTreeInput;
use App\Dto\TreeOutput;
use App\State\CreateTreeProcessor;
use App\State\TreesProvider;
use App\State\UpdateTreeProcessor;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/trees',
            output: TreeOutput::class,
            security: "is_granted('ROLE_USER')",
            provider: TreesProvider::class,
        ),
        new Post(
            uriTemplate: '/trees',
            input: CreateTreeInput::class,
            output: TreeOutput::class,
            security: "is_granted('ROLE_USER')",
            processor: CreateTreeProcessor::class,
        ),
        new Put(
            uriTemplate: '/trees/{id}',
            input: CreateTreeInput::class,
            output: TreeOutput::class,
            read: false,
            security: "is_granted('ROLE_USER')",
            processor: UpdateTreeProcessor::class,
        ),
    ],
)]
final class TreeCollection {}
