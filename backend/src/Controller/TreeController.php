<?php

namespace App\Controller;

use App\Entity\Tree;
use App\Form\TreeType;
use App\Repository\TreeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/tree')]
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
    public function new(Request $request, TreeRepository $treeRepository): Response
    {
        $tree = new Tree();
        $form = $this->createForm(TreeType::class, $tree);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $treeRepository->save($tree, true);

            return $this->redirectToRoute('app_tree_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('tree/new.html.twig', [
            'tree' => $tree,
            'form' => $form,
        ]);
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
