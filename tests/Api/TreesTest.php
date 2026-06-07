<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\Entity\Tree;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class TreesTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient([], ['HTTPS' => 'on']);
        $this->entityManager = self::getContainer()->get('doctrine')->getManager();

        $this->ensureSchemaExists();
        $this->deleteTestData();
    }

    protected function tearDown(): void
    {
        $this->deleteTestData();

        parent::tearDown();
    }

    public function testGetTreesRequiresAuthentication(): void
    {
        $this->client->jsonRequest('GET', '/api/trees');

        self::assertResponseStatusCodeSame(401);
    }

    public function testGetTreesReturnsOnlyAuthenticatedUserTrees(): void
    {
        $owner = $this->createUser('trees-owner@example.com', 'password');
        $otherUser = $this->createUser('trees-other@example.com', 'password');

        $this->createTree($owner, 'Owner older tree', new \DateTimeImmutable('2026-01-01'));
        $this->createTree($owner, 'Owner newer tree', new \DateTimeImmutable('2026-02-01'));
        $this->createTree($otherUser, 'Other tree', new \DateTimeImmutable('2026-03-01'));

        $this->client->jsonRequest('POST', '/api/auth', [
            'email' => 'trees-owner@example.com',
            'password' => 'password',
        ]);
        self::assertResponseIsSuccessful();

        $this->client->jsonRequest('GET', '/api/trees');

        self::assertResponseStatusCodeSame(200);
        self::assertResponseHeaderSame('content-type', 'application/json; charset=utf-8');

        $response = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

        self::assertCount(2, $response);
        self::assertSame(['Owner newer tree', 'Owner older tree'], array_column($response, 'name'));
        self::assertNotContains('Other tree', array_column($response, 'name'));

        foreach ($response as $tree) {
            self::assertArrayHasKey('id', $tree);
            self::assertArrayHasKey('name', $tree);
            self::assertArrayHasKey('createdAt', $tree);
            self::assertArrayNotHasKey('user', $tree);
            self::assertArrayNotHasKey('members', $tree);
        }
    }

    public function testGetTreeReturnsOwnedTree(): void
    {
        $user = $this->createUser('trees-viewer@example.com', 'password');
        $tree = $this->createTree($user, 'Viewed tree', new \DateTimeImmutable('2026-08-01'));
        $this->login('trees-viewer@example.com');

        $this->client->jsonRequest('GET', '/api/trees/' . $tree->getId());

        self::assertResponseStatusCodeSame(200);
        self::assertResponseHeaderSame('content-type', 'application/json; charset=utf-8');

        $response = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

        self::assertSame($tree->getId(), $response['id']);
        self::assertSame('Viewed tree', $response['name']);
        self::assertArrayHasKey('createdAt', $response);
        self::assertArrayNotHasKey('user', $response);
        self::assertArrayNotHasKey('members', $response);
    }

    public function testGetTreeRejectsTreeOwnedByAnotherUser(): void
    {
        $owner = $this->createUser('trees-owner-view@example.com', 'password');
        $viewer = $this->createUser('trees-other-viewer@example.com', 'password');
        $tree = $this->createTree($owner, 'View protected tree', new \DateTimeImmutable('2026-09-01'));
        $this->login($viewer->getEmail() ?? '');

        $this->client->jsonRequest('GET', '/api/trees/' . $tree->getId());

        self::assertResponseStatusCodeSame(403);
    }

    public function testGetTreeReturns404ForUnknownTree(): void
    {
        $this->createUser('trees-missing-viewer@example.com', 'password');
        $this->login('trees-missing-viewer@example.com');

        $this->client->jsonRequest('GET', '/api/trees/999999');

        self::assertResponseStatusCodeSame(404);
    }

    public function testPostTreesCreatesTreeOwnedByAuthenticatedUser(): void
    {
        $user = $this->createUser('trees-creator@example.com', 'password');
        $this->login('trees-creator@example.com');

        $this->client->jsonRequest('POST', '/api/trees', [
            'name' => 'Created tree',
        ]);

        self::assertResponseStatusCodeSame(201);
        self::assertResponseHeaderSame('content-type', 'application/json; charset=utf-8');

        $response = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('id', $response);
        self::assertSame('Created tree', $response['name']);
        self::assertArrayHasKey('createdAt', $response);
        self::assertArrayNotHasKey('user', $response);
        self::assertArrayNotHasKey('members', $response);

        $tree = $this->entityManager->getRepository(Tree::class)->find($response['id']);

        self::assertNotNull($tree);
        self::assertSame($user->getId(), $tree->getUser()?->getId());
    }

    public function testPostTreesRequiresCsrfProtection(): void
    {
        $this->client->jsonRequest('POST', '/api/trees', [
            'name' => 'Unauthenticated tree',
        ]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testPostTreesRejectsBlankName(): void
    {
        $this->createUser('trees-invalid@example.com', 'password');
        $this->login('trees-invalid@example.com');

        $this->client->jsonRequest('POST', '/api/trees', [
            'name' => '',
        ]);

        self::assertResponseStatusCodeSame(422);
    }

    public function testPutTreeUpdatesOwnedTree(): void
    {
        $user = $this->createUser('trees-editor@example.com', 'password');
        $tree = $this->createTree($user, 'Original tree', new \DateTimeImmutable('2026-04-01'));
        $this->login('trees-editor@example.com');

        $this->client->jsonRequest('PUT', '/api/trees/' . $tree->getId(), [
            'name' => 'Updated tree',
        ]);

        self::assertResponseStatusCodeSame(200);

        $response = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

        self::assertSame($tree->getId(), $response['id']);
        self::assertSame('Updated tree', $response['name']);
        self::assertArrayHasKey('createdAt', $response);

        $this->entityManager->clear();

        $updatedTree = $this->entityManager->getRepository(Tree::class)->find($tree->getId());

        self::assertSame('Updated tree', $updatedTree?->getName());
    }

    public function testPutTreeRejectsTreeOwnedByAnotherUser(): void
    {
        $owner = $this->createUser('trees-owner-edit@example.com', 'password');
        $editor = $this->createUser('trees-other-editor@example.com', 'password');
        $tree = $this->createTree($owner, 'Protected tree', new \DateTimeImmutable('2026-05-01'));
        $this->login($editor->getEmail() ?? '');

        $this->client->jsonRequest('PUT', '/api/trees/' . $tree->getId(), [
            'name' => 'Hacked tree',
        ]);

        self::assertResponseStatusCodeSame(403);

        $this->entityManager->clear();

        $protectedTree = $this->entityManager->getRepository(Tree::class)->find($tree->getId());

        self::assertSame('Protected tree', $protectedTree?->getName());
    }

    public function testPutTreeReturns404ForUnknownTree(): void
    {
        $this->createUser('trees-missing-editor@example.com', 'password');
        $this->login('trees-missing-editor@example.com');

        $this->client->jsonRequest('PUT', '/api/trees/999999', [
            'name' => 'Missing tree',
        ]);

        self::assertResponseStatusCodeSame(404);
    }

    public function testDeleteTreeDeletesOwnedTree(): void
    {
        $user = $this->createUser('trees-deleter@example.com', 'password');
        $tree = $this->createTree($user, 'Deleted tree', new \DateTimeImmutable('2026-06-01'));
        $this->login('trees-deleter@example.com');

        $this->client->jsonRequest('DELETE', '/api/trees/' . $tree->getId());

        self::assertResponseStatusCodeSame(204);

        $this->entityManager->clear();

        $deletedTree = $this->entityManager->getRepository(Tree::class)->find($tree->getId());

        self::assertNull($deletedTree);
    }

    public function testDeleteTreeRejectsTreeOwnedByAnotherUser(): void
    {
        $owner = $this->createUser('trees-owner-delete@example.com', 'password');
        $deleter = $this->createUser('trees-other-deleter@example.com', 'password');
        $tree = $this->createTree($owner, 'Delete protected tree', new \DateTimeImmutable('2026-07-01'));
        $this->login($deleter->getEmail() ?? '');

        $this->client->jsonRequest('DELETE', '/api/trees/' . $tree->getId());

        self::assertResponseStatusCodeSame(403);

        $this->entityManager->clear();

        $protectedTree = $this->entityManager->getRepository(Tree::class)->find($tree->getId());

        self::assertNotNull($protectedTree);
    }

    public function testDeleteTreeReturns404ForUnknownTree(): void
    {
        $this->createUser('trees-missing-deleter@example.com', 'password');
        $this->login('trees-missing-deleter@example.com');

        $this->client->jsonRequest('DELETE', '/api/trees/999999');

        self::assertResponseStatusCodeSame(404);
    }

    private function createUser(string $email, string $plainPassword): User
    {
        $user = new User()
            ->setEmail($email)
            ->setFirstname('Trees')
            ->setLastname('User')
            ->setIsVerified(true)
        ;

        $user->setPassword(
            self::getContainer()->get(UserPasswordHasherInterface::class)->hashPassword($user, $plainPassword),
        );

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    private function createTree(User $user, string $name, \DateTimeImmutable $createdAt): Tree
    {
        $tree = new Tree()
            ->setUser($user)
            ->setName($name)
            ->setCreatedAt($createdAt)
        ;

        $this->entityManager->persist($tree);
        $this->entityManager->flush();

        return $tree;
    }

    private function login(string $email): void
    {
        $this->client->jsonRequest('POST', '/api/auth', [
            'email' => $email,
            'password' => 'password',
        ]);
        self::assertResponseIsSuccessful();

        $csrfCookie = $this->getResponseCookie('csrf_token');
        self::assertNotNull($csrfCookie);

        $this->client->setServerParameter('HTTP_X_CSRF_TOKEN', $csrfCookie->getValue());
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

    private function deleteTestData(): void
    {
        $this->entityManager->createQuery('DELETE FROM App\Entity\Tree tree WHERE tree.name LIKE :pattern')
            ->setParameter('pattern', 'Owner %')
            ->execute()
        ;
        $this->entityManager->createQuery('DELETE FROM App\Entity\Tree tree WHERE tree.name = :name')
            ->setParameter('name', 'Other tree')
            ->execute()
        ;
        $this->entityManager->createQuery('DELETE FROM App\Entity\Tree tree WHERE tree.name IN (:names)')
            ->setParameter('names', ['Created tree', 'Unauthenticated tree', 'Original tree', 'Updated tree', 'Protected tree', 'Hacked tree', 'Deleted tree', 'Delete protected tree', 'Viewed tree', 'View protected tree'])
            ->execute()
        ;
        $this->entityManager->createQuery('DELETE FROM App\Entity\User user WHERE user.email LIKE :pattern')
            ->setParameter('pattern', 'trees-%')
            ->execute()
        ;
    }
}
