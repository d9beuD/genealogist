<?php

namespace App\Controller;

use App\Entity\Person;
use App\Entity\User;
use App\Form\PersonType;
use App\Form\TreeOptionsType;
use App\Service\FavoriteMemberManager;
use App\Service\ImageManager;
use App\Service\Tree\AncestorTreeViewModelBuilder;
use App\Service\Tree\PersonAncestorBranchMemberCollector;
use App\Service\Tree\TreeStatisticsBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class PersonController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $entityManager,
        private readonly ImageManager $imageManager,
        private readonly FavoriteMemberManager $favoriteMemberManager,
        private readonly AncestorTreeViewModelBuilder $ancestorTreeViewModelBuilder,
        private readonly PersonAncestorBranchMemberCollector $personAncestorBranchMemberCollector,
        private readonly TreeStatisticsBuilder $treeStatisticsBuilder,
        private readonly SerializerInterface $serializer,
        private readonly ChartBuilderInterface $chartBuilder,
    ) {
    }

    #[Route('/person/{id}', name: 'app_person_show', methods: ['GET'])]
    #[IsGranted('view', 'person')]
    public function show(Person $person): Response
    {
        return $this->render('person/show.html.twig', [
            'person' => $person,
        ]);
    }

    #[Route('/person/{id}/unions', name: 'app_person_unions', methods: ['GET'])]
    #[IsGranted('edit', 'person')]
    public function unions(Person $person): Response
    {
        return $this->render('person/unions.html.twig', [
            'person' => $person,
        ]);
    }

    #[Route('/person/{id}/edit', name: 'app_person_edit', methods: ['GET', 'POST'])]
    #[IsGranted('edit', 'person')]
    public function edit(
        Request $request,
        Person $person,
    ): Response {
        $form = $this->createForm(PersonType::class, $person);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$person->isDead()) {
                $person->setDeath(null);
                $person->setDeathDayUnsure(false);
                $person->setDeathMonthUnsure(false);
                $person->setDeathYearUnsure(false);
            }

            if ($form->get('portrait')->getData()) {
                $path = $this->imageManager->save($form->get('portrait')->getData(), $request);
                if ($person->getPortrait()) {
                    $this->imageManager->remove($person->getPortrait());
                }

                $person->setPortrait($path);
            }

            $this->entityManager->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('person.edit.success', ['name' => $person->getFullName()]),
            );

            return $this->redirectToRoute('app_person_show', ['id' => $person->getId()]);
        }

        return $this->render('person/edit.html.twig', [
            'person' => $person,
            'form' => $form,
            'tree' => $person->getTree(),
        ]);
    }

    #[Route('/person/{id}', name: 'app_person_delete', methods: ['POST'])]
    #[IsGranted('delete', 'person')]
    public function delete(
        Request $request,
        Person $person,
    ): Response {
        $tree = $person->getTree();

        if ($this->isCsrfTokenValid('delete'.$person->getId(), $request->request->get('_token'))) {
            if ($person->getPortrait()) {
                $this->imageManager->remove($person->getPortrait());
            }

            $this->entityManager->remove($person);
            $this->entityManager->flush();
            $this->addFlash(
                'success',
                $this->translator->trans('person.delete.success', ['name' => $person->getFullName()]),
            );
        } else {
            $this->addFlash(
                'danger',
                $this->translator->trans('person.delete.error', ['name' => $person->getFullName()]),
            );
        }

        return $this->redirectToRoute('app_tree_show', ['id' => $tree->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/person/{id}/tree', name: 'app_person_tree', methods: ['GET'])]
    #[IsGranted('view', 'person')]
    public function tree(Person $person, Request $request): Response
    {
        $form = $this->createForm(TreeOptionsType::class);
        $form->handleRequest($request);
        $depth = max(0, (int) ($form->get('depth')->getData() ?? 4));
        $tree = $this->ancestorTreeViewModelBuilder->build($person, $depth);

        return $this->render('person/show_tree.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
            'tree_data_json' => $this->serializer->serialize($tree, 'json', ['groups' => ['person_tree']]),
            'tree_view' => true,
        ]);
    }

    #[Route('/person/{id}/statistics', name: 'app_person_statistics', methods: ['GET'])]
    #[IsGranted('view', 'person')]
    public function statistics(Person $person): Response
    {
        $branchMembers = $this->personAncestorBranchMemberCollector->collect($person);
        $statistics = $this->treeStatisticsBuilder->buildFromMembers($branchMembers);

        return $this->render('person/statistics.html.twig', [
            'person' => $person,
            'statistics' => $statistics,
            'births_chart' => $this->createYearlyChart(
                $this->translator->trans('tree.statistics.births.chart_label'),
                $statistics['births_by_year'],
                'rgb(25, 135, 84)',
                'rgba(25, 135, 84, 0.18)',
            ),
            'deaths_chart' => $this->createYearlyChart(
                $this->translator->trans('tree.statistics.deaths.chart_label'),
                $statistics['deaths_by_year'],
                'rgb(220, 53, 69)',
                'rgba(220, 53, 69, 0.18)',
            ),
        ]);
    }

    #[Route('/person/{id}/favorite', name: 'app_person_favorite', methods: [Request::METHOD_GET])]
    public function favorite(Person $person, #[CurrentUser()] User $user): Response
    {
        $this->favoriteMemberManager->favorite($person, $user);

        $this->addFlash(
            'success',
            $this->translator->trans('person.favorite.added', ['name' => $person->getFullName()]),
        );

        return $this->redirectToRoute('app_tree_show', ['id' => $person->getTree()->getId()]);
    }

    #[Route('/person/{id}/unfavorite', name: 'app_person_unfavorite', methods: [Request::METHOD_GET])]
    public function unfavorite(Person $person, #[CurrentUser()] User $user): Response
    {
        $this->favoriteMemberManager->unfavorite($person, $user);

        $this->addFlash(
            'success',
            $this->translator->trans('person.favorite.removed', ['name' => $person->getFullName()]),
        );

        return $this->redirectToRoute('app_tree_show', ['id' => $person->getTree()->getId()]);
    }

    /**
     * @param array{labels: list<string>, data: list<int>} $dataset
     */
    private function createYearlyChart(string $label, array $dataset, string $borderColor, string $backgroundColor): ?Chart
    {
        if ([] === $dataset['labels']) {
            return null;
        }

        $chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => $dataset['labels'],
            'datasets' => [[
                'label' => $label,
                'data' => $dataset['data'],
                'borderColor' => $borderColor,
                'backgroundColor' => $backgroundColor,
                'fill' => true,
                'tension' => 0.25,
                'pointRadius' => 3,
                'pointHoverRadius' => 5,
            ]],
        ]);
        $chart->setOptions([
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
        ]);

        return $chart;
    }
}
