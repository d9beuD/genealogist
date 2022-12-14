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
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
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
        return $this->json($tree);
    }

    #[Route('/{id}', name: 'app_tree_edit', methods: ['PUT'])]
    public function edit(Request $request, Tree $tree, TreeRepository $treeRepository): Response
    {
        $tree->setName($request->get('name'));
        $treeRepository->save($tree, true);

        return $this->json($tree);
    }

    #[Route('/{id}', name: 'app_tree_delete', methods: ['DELETE'])]
    public function delete(Tree $tree, TreeRepository $treeRepository, #[CurrentUser] User $user): Response
    {
        if ($tree->getOwner()->getUserIdentifier() !== $user->getUserIdentifier()) {
            throw new AccessDeniedException();
        }

        $treeRepository->remove($tree, true);
        return $this->json($tree);
    }

    #[Route('/tree/{id}/members', name: 'app_tree_members', methods: ['GET'])]
    public function getMembers(Tree $tree): Response
    {
        return $this->json($tree->getMembers());
    }
}
