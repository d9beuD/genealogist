<?php

namespace App\Controller;

use App\Entity\Person;
use App\Entity\Tree;
use App\Entity\User;
use App\Form\MembersSearchType;
use App\Form\PersonType;
use App\Form\TreeType;
use App\Repository\PersonRepository;
use App\Service\ImageManager;
use App\Service\PersonManager;
use App\Service\Tree\TreeStatisticsBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class TreeController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $entityManager,
        private readonly ImageManager $imageManager,
        private readonly PersonRepository $personRepository,
        private readonly PersonManager $personManager,
        private readonly TreeStatisticsBuilder $treeStatisticsBuilder,
        private readonly ChartBuilderInterface $chartBuilder,
    ) {
    }

    #[Route('/project/', name: 'app_tree_index', methods: ['GET'])]
    public function index(#[CurrentUser()] User $currentUser): Response
    {
        return $this->render('tree/index.html.twig', [
            'trees' => $currentUser->getTrees(),
        ]);
    }

    #[Route('/project/new', name: 'app_tree_new', methods: ['GET', 'POST'])]
    public function new(#[CurrentUser()] User $user): Response
    {
        $total = $user->getTrees()->count();
        $tree = new Tree();
        $tree
            ->setUser($user)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setName('Family tree #'.($total + 1))
        ;

        $this->entityManager->persist($tree);
        $this->entityManager->flush();

        $this->addFlash(
            'success',
            $this->translator->trans('tree.new.success', ['name' => $tree->getName()])
        );

        return $this->redirectToRoute('app_tree_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/project/{id}', name: 'app_tree_show', methods: ['GET'])]
    public function show(Tree $tree, Request $request, #[CurrentUser()] User $user): Response
    {
        $form = $this->createForm(MembersSearchType::class);
        $form->handleRequest($request);

        $filters = $form->getData();
        $name = trim((string) ($filters['name'] ?? ''));
        $showWithoutOwnUnions = (bool) ($filters['withoutOwnUnions'] ?? false);
        $showWithoutParentUnion = (bool) ($filters['withoutParentUnion'] ?? false);
        $advancedFiltersActive = $showWithoutOwnUnions || $showWithoutParentUnion;

        $groupedMembers = $advancedFiltersActive
            ? []
            : $this->personManager->findGroupedByTree($tree, $name)
        ;

        $groupedMembersWithoutOwnUnions = $showWithoutOwnUnions
            ? $this->personManager->findGroupedWithoutOwnUnions($tree, $name)
            : []
        ;

        $groupedMembersWithoutParentUnion = $showWithoutParentUnion
            ? $this->personManager->findGroupedWithoutParentUnion($tree, $name)
            : []
        ;

        return $this->render('tree/show.html.twig', [
            'tree' => $tree,
            'form' => $form->createView(),
            'grouped_members' => $groupedMembers,
            'grouped_members_without_own_unions' => $groupedMembersWithoutOwnUnions,
            'grouped_members_without_parent_union' => $groupedMembersWithoutParentUnion,
            'advanced_filters_active' => $advancedFiltersActive,
            'show_without_own_unions' => $showWithoutOwnUnions,
            'show_without_parent_union' => $showWithoutParentUnion,
            'members_count' => array_sum(array_map('count', $groupedMembers)),
            'members_without_own_unions_count' => array_sum(array_map('count', $groupedMembersWithoutOwnUnions)),
            'members_without_parent_union_count' => array_sum(array_map('count', $groupedMembersWithoutParentUnion)),
            'favorites' => $this->personRepository->findFavoritesInTree($tree, $user),
        ]);
    }

    #[Route('/project/{id}/statistics', name: 'app_tree_statistics', methods: ['GET'])]
    #[IsGranted('view', 'tree')]
    public function statistics(Tree $tree): Response
    {
        $statistics = $this->treeStatisticsBuilder->build($tree);

        return $this->render('tree/statistics.html.twig', [
            'tree' => $tree,
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

    #[Route('/project/{id}/edit', name: 'app_tree_edit', methods: ['GET', 'POST'])]
    #[IsGranted('edit', 'tree')]
    public function edit(Request $request, Tree $tree): Response
    {
        $form = $this->createForm(TreeType::class, $tree);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('tree.edit.success', ['name' => $tree->getName()])
            );

            return $this->redirectToRoute('app_tree_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tree/edit.html.twig', [
            'tree' => $tree,
            'form' => $form,
        ]);
    }

    #[Route('/project/{id}', name: 'app_tree_delete', methods: ['POST'])]
    #[IsGranted('delete', 'tree')]
    public function delete(Request $request, Tree $tree): Response
    {
        if ($this->isCsrfTokenValid('delete'.$tree->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($tree);
            $this->entityManager->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('tree.delete.success', ['name' => $tree->getName()])
            );
        } else {
            $this->addFlash(
                'danger',
                $this->translator->trans('tree.delete.error', ['name' => $tree->getName()])
            );
        }

        return $this->redirectToRoute('app_tree_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/project/{id}/members', name: 'app_tree_members_add', methods: ['GET', 'POST'])]
    #[IsGranted('add_member', 'tree')]
    public function addMember(
        Request $request,
        Tree $tree,
    ): Response {
        $person = new Person();
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
                $person->setPortrait($path);
            }

            $tree->addMember($person);
            $this->entityManager->persist($person);
            $this->entityManager->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('tree.add_member.success', ['name' => $person->getFullName()])
            );

            return $this->redirectToRoute('app_tree_show', ['id' => $tree->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('person/new.html.twig', [
            'person' => $person,
            'form' => $form,
            'tree' => $tree,
        ]);
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
