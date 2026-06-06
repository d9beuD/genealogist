<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Application\Tree\UpdateTree;
use App\Application\Tree\UpdateTreeCommand;
use App\Dto\CreateTreeInput;
use App\Dto\TreeOutput;
use App\Repository\TreeRepository;
use App\Security\Voter\TreeVoter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProcessorInterface<CreateTreeInput, TreeOutput>
 */
final readonly class UpdateTreeProcessor implements ProcessorInterface
{
    public function __construct(
        private TreeRepository $trees,
        private Security $security,
        private UpdateTree $updateTree,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): TreeOutput
    {
        if (!$data instanceof CreateTreeInput) {
            throw new \InvalidArgumentException(\sprintf('Expected %s.', CreateTreeInput::class));
        }

        $tree = $this->trees->find((int) ($uriVariables['id'] ?? 0));

        if (null === $tree) {
            throw new NotFoundHttpException('Tree not found.');
        }

        if (!$this->security->isGranted(TreeVoter::EDIT, $tree)) {
            throw new AccessDeniedHttpException();
        }

        $tree = ($this->updateTree)(new UpdateTreeCommand(
            tree: $tree,
            name: $data->name,
        ));

        return new TreeOutput(
            id: $tree->getId() ?? 0,
            name: $tree->getName() ?? '',
            createdAt: $tree->getCreatedAt() ?? new \DateTimeImmutable('@0'),
        );
    }
}
