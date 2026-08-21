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
use App\Dto\PersonOutput;
use App\Dto\TreeOutput;
use App\Dto\ImportGedcomInput;
use App\Dto\GedcomImportOutput;
use App\State\CreateTreeProcessor;
use App\State\DeleteTreeProcessor;
use App\State\TreePeopleProvider;
use App\State\TreeProvider;
use App\State\TreesProvider;
use App\State\UpdateTreeProcessor;
use App\State\ImportGedcomProcessor;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/trees',
            security: "is_granted('ROLE_USER')",
            output: TreeOutput::class,
            provider: TreesProvider::class,
        ),
        new Get(
            uriTemplate: '/trees/{id}',
            security: "is_granted('ROLE_USER')",
            output: TreeOutput::class,
            provider: TreeProvider::class,
        ),
        new GetCollection(
            uriTemplate: '/trees/{id}/people',
            security: "is_granted('ROLE_USER')",
            output: PersonOutput::class,
            provider: TreePeopleProvider::class,
        ),
        new Post(
            uriTemplate: '/trees',
            security: "is_granted('ROLE_USER')",
            input: CreateTreeInput::class,
            output: TreeOutput::class,
            processor: CreateTreeProcessor::class,
        ),
        new Post(
            uriTemplate: '/trees/import',
            inputFormats: ['multipart' => ['multipart/form-data']],
            security: "is_granted('ROLE_USER')",
            input: ImportGedcomInput::class,
            output: GedcomImportOutput::class,
            deserialize: false,
            processor: ImportGedcomProcessor::class,
        ),
        new Put(
            uriTemplate: '/trees/{id}',
            security: "is_granted('ROLE_USER')",
            input: CreateTreeInput::class,
            output: TreeOutput::class,
            read: false,
            processor: UpdateTreeProcessor::class,
        ),
        new Delete(
            uriTemplate: '/trees/{id}',
            security: "is_granted('ROLE_USER')",
            read: false,
            processor: DeleteTreeProcessor::class,
        ),
    ],
)]
final class TreeCollection {}
