<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\Entity\Tree;
use App\Entity\Person;
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

    public function testGetTreePeopleRequiresAuthentication(): void
    {
        $this->client->jsonRequest('GET', '/api/trees/1/people');

        self::assertResponseStatusCodeSame(401);
    }

    public function testGetTreePeopleReturnsOwnedTreePeopleOrderedByName(): void
    {
        $owner = $this->createUser('trees-people-owner@example.com', 'password');
        $otherUser = $this->createUser('trees-people-other@example.com', 'password');
        $tree = $this->createTree($owner, 'People tree', new \DateTimeImmutable('2026-10-01'));
        $otherTree = $this->createTree($otherUser, 'Other people tree', new \DateTimeImmutable('2026-10-02'));

        $zoe = $this->createPerson($tree, 'Zoe', 'Brown');
        $alice = $this->createPerson($tree, 'Alice', 'Brown');
        $firstDuplicate = $this->createPerson($tree, 'Sam', 'Smith');
        $secondDuplicate = $this->createPerson($tree, 'Sam', 'Smith');
        $this->createPerson($otherTree, 'Aaron', 'Anderson');
        $this->login('trees-people-owner@example.com');

        $this->client->jsonRequest('GET', '/api/trees/' . $tree->getId() . '/people');

        self::assertResponseStatusCodeSame(200);
        self::assertResponseHeaderSame('content-type', 'application/json; charset=utf-8');

        $response = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

        self::assertCount(4, $response);
        self::assertSame(['Alice', 'Zoe', 'Sam', 'Sam'], array_column($response, 'firstname'));
        self::assertSame(['Brown', 'Brown', 'Smith', 'Smith'], array_column($response, 'lastname'));
        self::assertSame([$alice->getId(), $zoe->getId(), $firstDuplicate->getId(), $secondDuplicate->getId()], array_column($response, 'id'));

        foreach (['id', 'firstname', 'lastname', 'birth', 'death', 'birthDayUnsure', 'birthMonthUnsure', 'birthYearUnsure', 'deathDayUnsure', 'deathMonthUnsure', 'deathYearUnsure', 'portrait', 'bio', 'gender', 'dead', 'birthName', 'otherNames', 'birthPlace', 'deathPlace'] as $key) {
            self::assertArrayHasKey($key, $response[0]);
        }

        foreach (['tree', 'unions', 'parentUnion', 'sources', 'favorites'] as $key) {
            self::assertArrayNotHasKey($key, $response[0]);
        }
    }

    public function testGetTreePeopleRejectsTreeOwnedByAnotherUser(): void
    {
        $owner = $this->createUser('trees-people-protected-owner@example.com', 'password');
        $viewer = $this->createUser('trees-people-protected-viewer@example.com', 'password');
        $tree = $this->createTree($owner, 'Protected people tree', new \DateTimeImmutable('2026-10-03'));
        $this->login($viewer->getEmail() ?? '');

        $this->client->jsonRequest('GET', '/api/trees/' . $tree->getId() . '/people');

        self::assertResponseStatusCodeSame(403);
    }

    public function testGetTreePeopleReturns404ForUnknownTree(): void
    {
        $this->createUser('trees-people-missing-viewer@example.com', 'password');
        $this->login('trees-people-missing-viewer@example.com');

        $this->client->jsonRequest('GET', '/api/trees/999999/people');

        self::assertResponseStatusCodeSame(404);
    }

    public function testGetTreePeopleReturnsEmptyArrayForOwnedEmptyTree(): void
    {
        $owner = $this->createUser('trees-people-empty-owner@example.com', 'password');
        $tree = $this->createTree($owner, 'Empty people tree', new \DateTimeImmutable('2026-10-04'));
        $this->login('trees-people-empty-owner@example.com');

        $this->client->jsonRequest('GET', '/api/trees/' . $tree->getId() . '/people');

        self::assertResponseStatusCodeSame(200);
        self::assertSame([], json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR));
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

    private function createPerson(Tree $tree, string $firstname, string $lastname): Person
    {
        $person = new Person()
            ->setTree($tree)
            ->setFirstname($firstname)
            ->setLastname($lastname)
            ->setBirth(new \DateTimeImmutable('1980-01-02'))
            ->setDeath(new \DateTimeImmutable('2020-03-04'))
            ->setBirthDayUnsure(true)
            ->setBirthMonthUnsure(false)
            ->setBirthYearUnsure(true)
            ->setDeathDayUnsure(false)
            ->setDeathMonthUnsure(true)
            ->setDeathYearUnsure(false)
            ->setPortrait('portrait.jpg')
            ->setBio('Bio text')
            ->setGender(Person::OTHER)
            ->setDead(true)
            ->setBirthName($lastname . ' Birth')
            ->setOtherNames('Alias')
            ->setBirthPlace('Birth City')
            ->setDeathPlace('Death City')
        ;

        $this->entityManager->persist($person);
        $this->entityManager->flush();

        return $person;
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
        $this->entityManager->createQuery('DELETE FROM App\Entity\Person person WHERE person.tree IN (SELECT tree FROM App\Entity\Tree tree WHERE tree.name LIKE :pattern OR tree.name IN (:names))')
            ->setParameter('pattern', 'Owner %')
            ->setParameter('names', ['Other tree', 'Created tree', 'Unauthenticated tree', 'Original tree', 'Updated tree', 'Protected tree', 'Hacked tree', 'Deleted tree', 'Delete protected tree', 'Viewed tree', 'View protected tree', 'People tree', 'Other people tree', 'Protected people tree', 'Empty people tree'])
            ->execute()
        ;
        $this->entityManager->createQuery('DELETE FROM App\Entity\Tree tree WHERE tree.name LIKE :pattern')
            ->setParameter('pattern', 'Owner %')
            ->execute()
        ;
        $this->entityManager->createQuery('DELETE FROM App\Entity\Tree tree WHERE tree.name = :name')
            ->setParameter('name', 'Other tree')
            ->execute()
        ;
        $this->entityManager->createQuery('DELETE FROM App\Entity\Tree tree WHERE tree.name IN (:names)')
            ->setParameter('names', ['Created tree', 'Unauthenticated tree', 'Original tree', 'Updated tree', 'Protected tree', 'Hacked tree', 'Deleted tree', 'Delete protected tree', 'Viewed tree', 'View protected tree', 'People tree', 'Other people tree', 'Protected people tree', 'Empty people tree'])
            ->execute()
        ;
        $this->entityManager->createQuery('DELETE FROM App\Entity\User user WHERE user.email LIKE :pattern')
            ->setParameter('pattern', 'trees-%')
            ->execute()
        ;
    }
}
