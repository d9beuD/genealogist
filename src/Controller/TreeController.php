<?php

namespace App\Controller;

use App\Entity\Person;
use App\Entity\Tree;
use App\Entity\User;
use App\Form\MembersSearchType;
use App\Form\TreeType;
use App\Repository\TreeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/project')]
class TreeController extends AbstractController
{
    #[Route('/', name: 'app_tree_index', methods: ['GET'])]
    public function index(TreeRepository $treeRepository): Response
    {
        return $this->render('tree/index.html.twig', [
            'trees' => $treeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_tree_new', methods: ['GET', 'POST'])]
    public function new(EntityManagerInterface $entityManager, #[CurrentUser()] User $currentUser): Response
    {
        $tree = new Tree();
        $tree->setOwner($currentUser);
        $tree->setCreatedAt(new \DateTimeImmutable());
        
        $entityManager->persist($tree);
        $entityManager->flush();

        return $this->redirectToRoute('app_tree_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_tree_show', methods: ['GET'])]
    public function show(Tree $tree): Response
    {
        $form = $this->createForm(MembersSearchType::class);
        $members = $tree->getMembers();
        $groupedMembers = array_reduce($members->toArray(), function (array $groupedMembers, Person $member) {
            $firstLetter = strtoupper(mb_substr($member->getLastname(), 0, 1));
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

    #[Route('/{id}/edit', name: 'app_tree_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Tree $tree, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TreeType::class, $tree);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_tree_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tree/edit.html.twig', [
            'tree' => $tree,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_tree_delete', methods: ['POST'])]
    public function delete(Request $request, Tree $tree, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$tree->getId(), $request->request->get('_token'))) {
            $entityManager->remove($tree);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_tree_index', [], Response::HTTP_SEE_OTHER);
    }
}
