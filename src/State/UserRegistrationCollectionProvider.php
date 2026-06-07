<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\RegisteredUserOutput;

/**
 * Keep this provider while UserRegistration exists: API Platform needs a GET collection
 * operation in the entrypoint to stabilize frontend TypeScript type generation.
 *
 * @implements ProviderInterface<list<RegisteredUserOutput>>
 */
final readonly class UserRegistrationCollectionProvider implements ProviderInterface
{
    /**
     * @return list<RegisteredUserOutput>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        return [];
    }
}
