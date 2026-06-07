<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\Entity\RefreshToken;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthenticationTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient([], ['HTTPS' => 'on']);
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();

        $this->ensureSchemaExists();
        $this->deleteTestUsers();
        $this->deleteRefreshTokens();
    }

    protected function tearDown(): void
    {
        $this->deleteTestUsers();
        $this->deleteRefreshTokens();

        parent::tearDown();
    }

    public function testLoginSetsSecureHttpOnlyAccessAndRefreshCookies(): void
    {
        $this->createUser('api-user@example.com', 'password');

        $this->client->jsonRequest('POST', '/api/auth', [
            'email' => 'api-user@example.com',
            'password' => 'password',
        ]);

        self::assertResponseIsSuccessful();

        $accessTokenCookie = $this->getResponseCookie('BEARER');
        $refreshTokenCookie = $this->getResponseCookie('refresh_token');
        $csrfCookie = $this->getResponseCookie('csrf_token');

        self::assertNotNull($accessTokenCookie);
        self::assertTrue($accessTokenCookie->isSecure());
        self::assertTrue($accessTokenCookie->isHttpOnly());
        self::assertSame(Cookie::SAMESITE_STRICT, $accessTokenCookie->getSameSite());

        self::assertNotNull($refreshTokenCookie);
        self::assertTrue($refreshTokenCookie->isSecure());
        self::assertTrue($refreshTokenCookie->isHttpOnly());
        self::assertSame(Cookie::SAMESITE_STRICT, $refreshTokenCookie->getSameSite());

        self::assertNotNull($csrfCookie);
        self::assertTrue($csrfCookie->isSecure());
        self::assertFalse($csrfCookie->isHttpOnly());
        self::assertSame(Cookie::SAMESITE_STRICT, $csrfCookie->getSameSite());
    }

    public function testLoginRejectsInvalidCredentials(): void
    {
        $this->createUser('api-user@example.com', 'password');

        $this->client->jsonRequest('POST', '/api/auth', [
            'email' => 'api-user@example.com',
            'password' => 'wrong-password',
        ]);

        self::assertResponseStatusCodeSame(401);
        self::assertNull($this->getResponseCookie('BEARER'));
        self::assertNull($this->getResponseCookie('refresh_token'));
    }

    public function testRefreshTokenCookieIssuesNewAccessTokenCookie(): void
    {
        $this->createUser('api-user@example.com', 'password');

        $this->client->jsonRequest('POST', '/api/auth', [
            'email' => 'api-user@example.com',
            'password' => 'password',
        ]);
        self::assertResponseIsSuccessful();

        $csrfCookie = $this->getResponseCookie('csrf_token');
        self::assertNotNull($csrfCookie);

        $this->client->setServerParameter('HTTP_X_CSRF_TOKEN', $csrfCookie->getValue());
        $this->client->jsonRequest('POST', '/api/token/refresh');

        self::assertResponseIsSuccessful();

        $accessTokenCookie = $this->getResponseCookie('BEARER');
        $refreshTokenCookie = $this->getResponseCookie('refresh_token');

        self::assertNotNull($accessTokenCookie);
        self::assertTrue($accessTokenCookie->isSecure());
        self::assertTrue($accessTokenCookie->isHttpOnly());
        self::assertSame(Cookie::SAMESITE_STRICT, $accessTokenCookie->getSameSite());

        self::assertNotNull($refreshTokenCookie);
        self::assertTrue($refreshTokenCookie->isSecure());
        self::assertTrue($refreshTokenCookie->isHttpOnly());
        self::assertSame(Cookie::SAMESITE_STRICT, $refreshTokenCookie->getSameSite());
    }

    public function testRefreshTokenCookieRequiresCsrfHeader(): void
    {
        $this->createUser('api-user@example.com', 'password');

        $this->client->jsonRequest('POST', '/api/auth', [
            'email' => 'api-user@example.com',
            'password' => 'password',
        ]);
        self::assertResponseIsSuccessful();

        $this->client->jsonRequest('POST', '/api/token/refresh');

        self::assertResponseStatusCodeSame(403);
    }

    private function createUser(string $email, string $plainPassword): void
    {
        $user = new User()
            ->setEmail($email)
            ->setFirstname('API')
            ->setLastname('User')
            ->setIsVerified(true)
        ;

        $user->setPassword(
            static::getContainer()->get(UserPasswordHasherInterface::class)->hashPassword($user, $plainPassword),
        );

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    private function getResponseCookie(string $name): ?Cookie
    {
        foreach ($this->client->getResponse()->headers->getCookies() as $cookie) {
            if ($cookie->getName() === $name) {
                return $cookie;
            }
        }

        return null;
    }

    private function ensureSchemaExists(): void
    {
        $schemaTool = new SchemaTool($this->entityManager);
        $schemaTool->updateSchema($this->entityManager->getMetadataFactory()->getAllMetadata());
    }

    private function deleteTestUsers(): void
    {
        $this->entityManager->createQuery('DELETE FROM App\Entity\User user WHERE user.email = :email')
            ->setParameter('email', 'api-user@example.com')
            ->execute()
        ;
    }

    private function deleteRefreshTokens(): void
    {
        $this->entityManager->createQuery('DELETE FROM ' . RefreshToken::class . ' token')->execute();
    }
}
