<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\PersonOutput;
use App\Entity\Person;
use App\Repository\PersonRepository;
use App\Repository\TreeRepository;
use App\Security\Voter\TreeVoter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProviderInterface<array<int, PersonOutput>>
 */
final readonly class TreePeopleProvider implements ProviderInterface
{
    public function __construct(
        private TreeRepository $trees,
        private PersonRepository $people,
        private Security $security,
    ) {}

    /**
     * @return array<int, PersonOutput>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $tree = $this->trees->find((int) ($uriVariables['id'] ?? 0));

        if (null === $tree) {
            throw new NotFoundHttpException('Tree not found.');
        }

        if (!$this->security->isGranted(TreeVoter::VIEW, $tree)) {
            throw new AccessDeniedHttpException();
        }

        $people = $this->people->findByTreeOrderedByName($tree);

        return array_map(static fn(Person $person): PersonOutput => new PersonOutput(
            id: $person->getId() ?? 0,
            firstname: $person->getFirstname(),
            lastname: $person->getLastname(),
            birth: $person->getBirth(),
            death: $person->getDeath(),
            birthDayUnsure: (bool) $person->isBirthDayUnsure(),
            birthMonthUnsure: (bool) $person->isBirthMonthUnsure(),
            birthYearUnsure: (bool) $person->isBirthYearUnsure(),
            deathDayUnsure: (bool) $person->isDeathDayUnsure(),
            deathMonthUnsure: (bool) $person->isDeathMonthUnsure(),
            deathYearUnsure: (bool) $person->isDeathYearUnsure(),
            portrait: $person->getPortrait(),
            bio: $person->getBio(),
            gender: $person->getGender(),
            dead: (bool) $person->isDead(),
            birthName: $person->getBirthName(),
            otherNames: $person->getOtherNames(),
            birthPlace: $person->getBirthPlace(),
            deathPlace: $person->getDeathPlace(),
        ), $people);
    }
}
