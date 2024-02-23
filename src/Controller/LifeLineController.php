<?php

namespace App\Controller;

use App\Entity\Person;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class LifeLineController extends AbstractController
{
    #[Route('/person/{id}/life-line', name: 'app_person_life_line')]
    public function index(Person $person, TranslatorInterface $translator): Response
    {
        $events = [];

        // Get birth event
        if ($person->getBirth()) {
            $hasBirthDate = !!$person->getBirth();

            $events[] = [
                'date' => $person->getBirth(),
                'type' => 'birth',
                'label' => $translator->trans('life_line.birth.label'),
                'message' => $translator->trans('life_line.birth.message', [
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
            $hasUnionDate = !!$union->getWeddingDate();

            $events[] = [
                'date' => $union->getWeddingDate(),
                'type' => 'union',
                'label' => $translator->trans('life_line.union.label'),
                'message' => $translator->trans('life_line.union.message', [
                    'name' => $person->getFullName(),
                    'gender' => $person->getGender(),
                    'partner' => $hasPartner ? $union->getPartner($person)->getFullName() : '?',
                    'place' => $union->getWeddingPlace() ?? 'empty',
                    'year' => $hasUnionDate ? $union->getWeddingDate()->format('Y') : 'empty',
                ]),
            ];

            // Get child events
            foreach ($union->getChildren() as $child) {
                $hasBirthDate = !!$child->getBirth();
                $events[] = [
                    'date' => $child->getBirth(),
                    'type' => 'child',
                    'label' => $translator->trans('life_line.child.label'),
                    'message' => $translator->trans('life_line.child.message', [
                        'name' => $person->getFullName(),
                        'child' => $child->getFullName(),
                        'year' => $hasBirthDate ? $child->getBirth()->format('Y') : 'empty',
                    ]),
                ];
            }
        }

        // Get death event
        if ($person->getDeath()) {
            $events[] = [
                'date' => $person->getDeath(),
                'type' => 'death',
                'label' => $translator->trans('life_line.death.label'),
                'message' => $translator->trans('life_line.death.message', [
                    'name' => $person->getFullName(),
                    'gender' => $person->getGender(),
                    'year' => $person->getDeath()->format('Y'),
                    'place' => $person->getDeathPlace() ?? 'empty',
                ]),
            ];
        }

        $allEventsHaveDate = array_reduce($events, function ($carry, $event) {
            return $carry && !!$event['date'];
        }, true);

        if ($allEventsHaveDate) {
            usort($events, function ($a, $b) {
                return $a['date'] <=> $b['date'];
            });
        }

        return $this->render('life_line/index.html.twig', [
            'person' => $person,
            'events' => $events,
            'all_events_have_date' => $allEventsHaveDate,
        ]);
    }
}
