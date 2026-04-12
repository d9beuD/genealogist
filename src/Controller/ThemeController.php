<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ThemeController extends AbstractController
{
    public function __construct(private readonly \Psr\Log\LoggerInterface $logger)
    {
    }
    #[Route('/settings/theme', name: 'app_theme', methods: ['post'])]
    public function setMode(Request $request): Response
    {
        $response = new Response();
        $data = json_decode($request->getContent(), true);
        $mode = $data['mode'] ?? 'auto';
        $expires = new \DateTime('+30 days');

        $response->headers->setCookie(
            new Cookie('theme', $mode, $expires)
        );

        return $response;
    }
}
