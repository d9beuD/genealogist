<?php

namespace App\Controller;

use App\Entity\Person;
use App\Entity\Tree;
use App\Entity\User;
use App\Form\MembersSearchType;
use App\Form\PersonType;
use App\Form\TreeType;
use App\Service\ImageManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class TreeController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator, private readonly EntityManagerInterface $entityManager, private readonly ImageManager $imageManager,
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
            ->setOwner($user)
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
    public function show(Tree $tree, Request $request): Response
    {
        $form = $this->createForm(MembersSearchType::class);
        $form->handleRequest($request);

        $members = $tree->getMembers();

        // Filtre de recherche
        if ($form->isSubmitted() && $form->isValid()) {
            $name = mb_strtoupper((string) $form->get('name')->getData());
            $members = $members->filter(function (Person $member) use ($name): bool {
                if ('' !== $name && '0' !== $name) {
                    return str_contains(mb_strtoupper($member->getFullName()), $name);
                }

                return true;
            });
        }

        // Tri par ordre alphabétique
        $orderedMembers = $members->toArray();
        usort($orderedMembers, fn ($a, $b): int => strcmp(trim($a->getFullName()), trim($b->getFullName())));

        // Groupement par première lettre
        $groupedMembers = array_reduce($orderedMembers, function (array $groupedMembers, Person $member): array {
            $firstLetter = strtoupper(mb_substr(trim($member->getFullName()), 0, 1));
            $groupedMembers[$firstLetter][] = $member;

            return $groupedMembers;
        }, []);

        return $this->render('tree/show.html.twig', [
            'tree' => $tree,
            'form' => $form->createView(),
            'grouped_members' => $groupedMembers,
            'members_count' => $members->count(),
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
}
