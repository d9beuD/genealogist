<?php

namespace App\Controller;

use App\Entity\Person;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class LifeLineController extends AbstractController
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    #[Route('/person/{id}/life-line', name: 'app_person_life_line')]
    public function index(Person $person): Response
    {
        $events = [];

        // Get birth event
        if ($person->getBirth() instanceof \DateTimeInterface) {
            $hasBirthDate = (bool) $person->getBirth();

            $events[] = [
                'date' => $person->getBirth(),
                'type' => 'birth',
                'label' => $this->translator->trans('life_line.birth.label'),
                'message' => $this->translator->trans('life_line.birth.message', [
                    'name' => $person->getFullName(),
                    'gender' => $person->getGender(),
                    'year' => $hasBirthDate ? $person->getBirth()->format('Y') : 'empty',
                    'place' => $person->getBirthPlace() ?? 'empty',
                ]),
            ];
        }

        // Get union events
        foreach ($person->getUnions() as $union) {
            $hasPartner = $union->getPeople()->count() > 1;
            $hasUnionDate = (bool) $union->getStartsAt();

            $events[] = [
                'date' => $union->getStartsAt(),
                'type' => 'union',
                'label' => $this->translator->trans('life_line.union.label'),
                'message' => $this->translator->trans('life_line.union.message', [
                    'name' => $person->getFullName(),
                    'gender' => $person->getGender(),
                    'partner' => $hasPartner ? $union->getPartner($person)->getFullName() : '?',
                    'partner_path' => $hasPartner ? $this->generateUrl('app_person_life_line', ['id' => $union->getPartner($person)->getId()]) : '#',
                    'place' => $union->getPlace() ?? 'empty',
                    'year' => $hasUnionDate ? $union->getStartsAt()->format('Y') : 'empty',
                ]),
            ];

            // Get child events
            foreach ($union->getChildren() as $child) {
                $hasBirthDate = (bool) $child->getBirth();
                $events[] = [
                    'date' => $child->getBirth(),
                    'type' => 'child',
                    'label' => $this->translator->trans('life_line.child.label'),
                    'message' => $this->translator->trans('life_line.child.message', [
                        'name' => $person->getFullName(),
                        'child' => $child->getFullName(),
                        'child_path' => $this->generateUrl('app_person_life_line', ['id' => $child->getId()]),
                        'year' => $hasBirthDate ? $child->getBirth()->format('Y') : 'empty',
                    ]),
                ];
            }
        }

        // Get death event
        if ($person->getDeath() instanceof \DateTimeInterface) {
            $events[] = [
                'date' => $person->getDeath(),
                'type' => 'death',
                'label' => $this->translator->trans('life_line.death.label'),
                'message' => $this->translator->trans('life_line.death.message', [
                    'name' => $person->getFullName(),
                    'gender' => $person->getGender(),
                    'year' => $person->getDeath()->format('Y'),
                    'place' => $person->getDeathPlace() ?? 'empty',
                ]),
            ];
        }

        $allEventsHaveDate = array_reduce($events, fn ($carry, $event): bool => $carry && (bool) $event['date'], true);

        if ($allEventsHaveDate) {
            usort($events, fn (array $a, array $b): int => $a['date'] <=> $b['date']);
        }

        return $this->render('life_line/index.html.twig', [
            'person' => $person,
            'events' => $events,
            'all_events_have_date' => $allEventsHaveDate,
        ]);
    }
}
