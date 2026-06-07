<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Application\Tree\CreateTree;
use App\Application\Tree\CreateTreeCommand;
use App\Dto\CreateTreeInput;
use App\Dto\TreeOutput;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @implements ProcessorInterface<CreateTreeInput, TreeOutput>
 */
final readonly class CreateTreeProcessor implements ProcessorInterface
{
    public function __construct(
        private Security $security,
        private CreateTree $createTree,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): TreeOutput
    {
        if (!$data instanceof CreateTreeInput) {
            throw new \InvalidArgumentException(\sprintf('Expected %s.', CreateTreeInput::class));
        }

        $user = $this->security->getUser();

        if (!$user instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $tree = ($this->createTree)(new CreateTreeCommand(
            owner: $user,
            name: $data->name,
        ));

        return new TreeOutput(
            id: $tree->getId() ?? 0,
            name: $tree->getName() ?? '',
            createdAt: $tree->getCreatedAt() ?? new \DateTimeImmutable('@0'),
        );
    }
}
