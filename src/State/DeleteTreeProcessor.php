<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Application\Tree\DeleteTree;
use App\Application\Tree\DeleteTreeCommand;
use App\Repository\TreeRepository;
use App\Security\Voter\TreeVoter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProcessorInterface<object, void>
 */
final readonly class DeleteTreeProcessor implements ProcessorInterface
{
    public function __construct(
        private TreeRepository $trees,
        private Security $security,
        private DeleteTree $deleteTree,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        $tree = $this->trees->find((int) ($uriVariables['id'] ?? 0));

        if (null === $tree) {
            throw new NotFoundHttpException('Tree not found.');
        }

        if (!$this->security->isGranted(TreeVoter::DELETE, $tree)) {
            throw new AccessDeniedHttpException();
        }

        ($this->deleteTree)(new DeleteTreeCommand($tree));
    }
}
