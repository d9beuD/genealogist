<?php

declare(strict_types=1);

namespace App\Service\Tree;

use App\Entity\Person;
use App\Entity\Union;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class AncestorTreeViewModelBuilder
{
    public function __construct(
        private Packages $packages,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function build(Person $person, int $maxDepth): AncestorTreeNodeViewModel
    {
        return $this->buildNode($person, $maxDepth, 'root', 1);
    }

    private function buildNode(Person $person, int $maxDepth, string $occurrenceId, int $depth): AncestorTreeNodeViewModel
    {
        return new AncestorTreeNodeViewModel(
            occurrenceId: $occurrenceId,
            personId: (int) $person->getId(),
            fullName: $person->getFullName(),
            profileUrl: $this->urlGenerator->generate('app_person_show', ['id' => $person->getId()]),
            yearsLabel: $this->formatYearsLabel($person),
            portraitUrl: $this->buildPortraitUrl($person),
            gender: $person->getGender(),
            parentUnion: $this->buildParentUnion($person, $maxDepth, $occurrenceId, $depth),
        );
    }

    private function buildParentUnion(Person $person, int $maxDepth, string $occurrenceId, int $depth): ?AncestorTreeUnionViewModel
    {
        $parentUnion = $person->getParentUnion();

        if (null === $parentUnion || (0 !== $maxDepth && $depth >= $maxDepth)) {
            return null;
        }

        $parents = $parentUnion->getPeople()->toArray();
        usort($parents, static fn (Person $left, Person $right): int => ($left->getGender() ?? -1) <=> ($right->getGender() ?? -1));
        $parents = array_values(array_reverse($parents));

        return new AncestorTreeUnionViewModel(
            unionId: (int) $parentUnion->getId(),
            startsAtLabel: $this->formatUnionStart($parentUnion),
            parents: array_map(
                fn (Person $parent, int $index): AncestorTreeNodeViewModel => $this->buildNode(
                    $parent,
                    $maxDepth,
                    sprintf('%s-%d-%d', $occurrenceId, (int) $parentUnion->getId(), $index),
                    $depth + 1,
                ),
                $parents,
                array_keys($parents),
            ),
        );
    }

    private function formatYearsLabel(Person $person): ?string
    {
        $birthYear = $person->getBirth()?->format('Y');
        $deathYear = $person->getDeath()?->format('Y');

        if (null === $birthYear && null === $deathYear) {
            return null;
        }

        if (null !== $birthYear && null !== $deathYear) {
            return sprintf('%s • %s', $birthYear, $deathYear);
        }

        return $birthYear ?? $deathYear;
    }

    private function buildPortraitUrl(Person $person): ?string
    {
        if (null === $person->getPortrait()) {
            return null;
        }

        return $this->packages->getUrl('pictures/'.$person->getPortrait());
    }

    private function formatUnionStart(Union $union): ?string
    {
        return $union->getStartsAt()?->format('d/m/Y');
    }
}
