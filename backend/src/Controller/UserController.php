<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return $this->json($userRepository->findAll());
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        // If the asked user does not belong to the current user, we deny access
        // if not admin
        if ($this->getUser()->getUserIdentifier() !== $user->getUserIdentifier()) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');
        }

        return $this->json([$user]);
    }

    #[Route('/{id}', name: 'app_user_edit', methods: ['PUT'])]
    public function edit(Request $request, User $user, UserRepository $userRepository): JsonResponse
    {
        // If the asked user does not belong to the current user, we deny access
        // if not admin
        if ($this->getUser()->getUserIdentifier() !== $user->getUserIdentifier()) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');
        }

        // The form must be complete
        if (
            !$request->get('email')
            || !$request->get('firstname')
            || !$request->get('lastname')
        ) {
            throw new BadRequestHttpException('Email is required');
        }

        $user->setEmail($request->get('email'))
            ->setFirstname($request->get('firstname'))
            ->setLastname($request->get('lastname'));

        $userRepository->save($user, true);

        return $this->json($user);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['DELETE'])]
    public function delete(User $user, UserRepository $userRepository): JsonResponse
    {
        // If the asked user does not belong to the current user, we deny access
        // if not admin
        if ($this->getUser()->getUserIdentifier() !== $user->getUserIdentifier()) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');
        }

        $userRepository->delete($user);

        return $this->json($user);
    }
}
