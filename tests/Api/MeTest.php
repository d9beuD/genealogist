<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class MeTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient([], ['HTTPS' => 'on']);
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();

        $this->ensureSchemaExists();
        $this->deleteTestUsers();
    }

    protected function tearDown(): void
    {
        $this->deleteTestUsers();

        parent::tearDown();
    }

    public function testGetMeReturnsUserIdentity(): void
    {
        $this->createUser('me-user@example.com', 'password');

        $this->client->jsonRequest('POST', '/api/auth', [
            'email' => 'me-user@example.com',
            'password' => 'password',
        ]);
        self::assertResponseIsSuccessful();

        $this->client->jsonRequest('GET', '/api/me');

        self::assertResponseStatusCodeSame(200);
        self::assertResponseHeaderSame('content-type', 'application/json; charset=utf-8');

        $response = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('id', $response);
        self::assertArrayHasKey('email', $response);
        self::assertArrayHasKey('firstname', $response);
        self::assertArrayHasKey('lastname', $response);
        self::assertArrayHasKey('roles', $response);

        self::assertSame('me-user@example.com', $response['email']);
        self::assertSame('Me', $response['firstname']);
        self::assertSame('User', $response['lastname']);
        self::assertIsArray($response['roles']);
        self::assertContains('ROLE_USER', $response['roles']);
    }

    public function testGetMeExcludesSensitiveFields(): void
    {
        $this->createUser('me-user2@example.com', 'password');

        $this->client->jsonRequest('POST', '/api/auth', [
            'email' => 'me-user2@example.com',
            'password' => 'password',
        ]);
        self::assertResponseIsSuccessful();

        $this->client->jsonRequest('GET', '/api/me');

        self::assertResponseStatusCodeSame(200);
        $response = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

        self::assertArrayNotHasKey('password', $response);
        self::assertArrayNotHasKey('plainPassword', $response);
        self::assertArrayNotHasKey('isVerified', $response);
        self::assertArrayNotHasKey('trees', $response);
        self::assertArrayNotHasKey('favorites', $response);
    }

    public function testGetMeReturns401WhenUnauthenticated(): void
    {
        $this->client->jsonRequest('GET', '/api/me');

        self::assertResponseStatusCodeSame(401);
    }

    public function testGetMeReturnsResolvedRolesViaRoleHierarchy(): void
    {
        $user = new User()
            ->setEmail('me-hierarchy@example.com')
            ->setFirstname('Hierarchy')
            ->setLastname('User')
            ->setIsVerified(true)
        ;

        $user->setPassword(
            static::getContainer()->get(UserPasswordHasherInterface::class)->hashPassword($user, 'password'),
        );
        $user->setRoles(['ROLE_ADMIN']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->client->jsonRequest('POST', '/api/auth', [
            'email' => 'me-hierarchy@example.com',
            'password' => 'password',
        ]);
        self::assertResponseIsSuccessful();

        $this->client->jsonRequest('GET', '/api/me');

        self::assertResponseStatusCodeSame(200);
        $response = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

        self::assertIsArray($response['roles']);
    }

    private function createUser(string $email, string $plainPassword): void
    {
        $user = new User()
            ->setEmail($email)
            ->setFirstname('Me')
            ->setLastname('User')
            ->setIsVerified(true)
        ;

        $user->setPassword(
            static::getContainer()->get(UserPasswordHasherInterface::class)->hashPassword($user, $plainPassword),
        );

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    private function ensureSchemaExists(): void
    {
        $schemaTool = new SchemaTool($this->entityManager);
        $schemaTool->updateSchema($this->entityManager->getMetadataFactory()->getAllMetadata());
    }

    private function deleteTestUsers(): void
    {
        $this->entityManager->createQuery('DELETE FROM App\Entity\User user WHERE user.email LIKE :pattern')
            ->setParameter('pattern', 'me-%')
            ->execute()
        ;
    }
}
