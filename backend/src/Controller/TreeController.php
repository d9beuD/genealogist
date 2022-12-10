<?php

namespace App\Controller;

use App\Entity\Tree;
use App\Entity\User;
use App\Form\TreeType;
use App\Repository\TreeRepository;
use App\Service\PaginationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/tree')]
class TreeController extends AbstractController
{
    #[Route('/', name: 'app_tree_index', methods: ['GET'])]
    public function index(TreeRepository $treeRepository, Request $request): Response
    {
        $trees = $treeRepository->findAllPaginated($request);

        return $this->json([
            'data' => $trees,
            'count' => $treeRepository->findAllCount(),
            'limit' => PaginationService::getLimit($request),
            'offset' => PaginationService::getOffset($request),
        ]);
    }

    #[Route('', name: 'app_tree_new', methods: ['POST'])]
    public function new(Request $request, TreeRepository $treeRepository, #[CurrentUser] ?User $user): Response
    {
        $tree = new Tree();
        $tree
            ->setName($request->get('name'))
            ->setOwner($user);

        $treeRepository->save($tree, true);

        return $this->json($tree);
    }

    #[Route('/{id}', name: 'app_tree_show', methods: ['GET'])]
    public function show(Tree $tree): Response
    {
        return $this->render('tree/show.html.twig', [
            'tree' => $tree,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_tree_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Tree $tree, TreeRepository $treeRepository): Response
    {
        $form = $this->createForm(TreeType::class, $tree);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $treeRepository->save($tree, true);

            return $this->redirectToRoute('app_tree_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('tree/edit.html.twig', [
            'tree' => $tree,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_tree_delete', methods: ['POST'])]
    public function delete(Request $request, Tree $tree, TreeRepository $treeRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$tree->getId(), $request->request->get('_token'))) {
            $treeRepository->remove($tree, true);
        }

        return $this->redirectToRoute('app_tree_index', [], Response::HTTP_SEE_OTHER);
    }
}
