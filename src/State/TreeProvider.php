<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\TreeOutput;
use App\Repository\TreeRepository;
use App\Security\Voter\TreeVoter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProviderInterface<TreeOutput>
 */
final readonly class TreeProvider implements ProviderInterface
{
    public function __construct(
        private TreeRepository $trees,
        private Security $security,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): TreeOutput
    {
        $tree = $this->trees->find((int) ($uriVariables['id'] ?? 0));

        if (null === $tree) {
            throw new NotFoundHttpException('Tree not found.');
        }

        if (!$this->security->isGranted(TreeVoter::VIEW, $tree)) {
            throw new AccessDeniedHttpException();
        }

        return new TreeOutput(
            id: $tree->getId() ?? 0,
            name: $tree->getName() ?? '',
            createdAt: $tree->getCreatedAt() ?? new \DateTimeImmutable('@0'),
        );
    }
}
