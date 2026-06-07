<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Dto\CreateTreeInput;
use App\Dto\TreeOutput;
use App\State\CreateTreeProcessor;
use App\State\DeleteTreeProcessor;
use App\State\TreeProvider;
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
        new Get(
            uriTemplate: '/trees/{id}',
            output: TreeOutput::class,
            security: "is_granted('ROLE_USER')",
            provider: TreeProvider::class,
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
        new Delete(
            uriTemplate: '/trees/{id}',
            read: false,
            security: "is_granted('ROLE_USER')",
            processor: DeleteTreeProcessor::class,
        ),
    ],
)]
final class TreeCollection {}
