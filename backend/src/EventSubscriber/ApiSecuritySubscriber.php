<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class ApiSecuritySubscriber implements EventSubscriberInterface
{
    private const string CSRF_COOKIE_NAME = 'csrf_token';

    private const string CSRF_HEADER_NAME = 'X-CSRF-Token';

    /**
     * @var array<string, true>
     */
    private const array SAFE_METHODS = [
        'GET' => true,
        'HEAD' => true,
        'OPTIONS' => true,
        'TRACE' => true,
    ];

    /**
     * @return array<string, int[]|string[]>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['validateCsrfToken', 8],
            KernelEvents::RESPONSE => ['hardenResponse', -8],
        ];
    }

    public function validateCsrfToken(RequestEvent $requestEvent): void
    {
        if (!$requestEvent->isMainRequest()) {
            return;
        }

        $request = $requestEvent->getRequest();
        if (!$this->requiresCsrfProtection($request->getPathInfo(), $request->getMethod())) {
            return;
        }

        $cookieToken = $request->cookies->get(self::CSRF_COOKIE_NAME);
        $headerToken = $request->headers->get(self::CSRF_HEADER_NAME);

        if (!\is_string($cookieToken) || !\is_string($headerToken) || !hash_equals($cookieToken, $headerToken)) {
            $requestEvent->setResponse(new JsonResponse(['message' => 'Invalid CSRF token.'], JsonResponse::HTTP_FORBIDDEN));
        }
    }

    public function hardenResponse(ResponseEvent $responseEvent): void
    {
        if (!$responseEvent->isMainRequest()) {
            return;
        }

        $response = $responseEvent->getResponse();
        $headers = $response->headers;

        $headers->set('X-Content-Type-Options', 'nosniff');
        $headers->set('X-Frame-Options', 'DENY');
        $headers->set('Referrer-Policy', 'no-referrer');
        $headers->set('Content-Security-Policy', "default-src 'none'; frame-ancestors 'none'");
        $headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

        $request = $responseEvent->getRequest();
        if (!\in_array($request->getPathInfo(), ['/api/auth', '/api/token/refresh'], true) || !$response->isSuccessful()) {
            return;
        }

        $headers->setCookie(
            Cookie::create(self::CSRF_COOKIE_NAME)
            ->withValue(bin2hex(random_bytes(32)))
            ->withPath('/')
            ->withSecure(true)
            ->withHttpOnly(false)
            ->withSameSite(Cookie::SAMESITE_STRICT)
        );
    }

    private function requiresCsrfProtection(string $path, string $method): bool
    {
        if (isset(self::SAFE_METHODS[$method])) {
            return false;
        }

        if (\in_array($path, ['/api/auth', '/api/register'], true) && $method === 'POST') {
            return false;
        }

        return str_starts_with($path, '/api/');
    }
}
