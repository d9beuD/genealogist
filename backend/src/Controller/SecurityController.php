<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class SecurityController extends AbstractController
{
    #[Route('/registration', name: 'app_registration', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, UserRepository $userRepository): Response
    {
        if (!$request->get('email')) {
            throw new BadRequestHttpException('Email is required');
        }

        if (!$request->get('password')) {
            throw new BadRequestHttpException('Password is required');
        }

        if (!$request->get('firstname')) {
            throw new BadRequestHttpException('Firstname is required');
        }

        if (!$request->get('lastname')) {
            throw new BadRequestHttpException('Lastname is required');
        }

        $user = new User();
        $user->setEmail($request->get('email'))
            ->setFirstname($request->get('firstname'))
            ->setLastname($request->get('lastname'));

        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $request->get('password'),
        );
        $user->setPassword($hashedPassword);

        $userRepository->save($user, true);

        return $this->json($user);
    }

    #[Route('/login', name: 'app_login', methods: ['POST'])]
    public function login(#[CurrentUser] ?User $user): Response
    {
        if (null === $user) {
            return $this->json([
                'message' => 'Missing credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json($user);
    }

    #[Route('/logout', name: 'app_logout', methods: ['GET'])]
    public function logout()
    {
    }

    #[Route('/session', name: 'app_session', methods: ['GET'])]
    public function session(#[CurrentUser] ?User $user): JsonResponse
    {
        return $this->json($user);
    }
}
