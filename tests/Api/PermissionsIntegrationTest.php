<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\Entity\Person;
use App\Entity\Tree;
use App\Entity\User;
use App\Service\ResourcePermissions;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class PermissionsIntegrationTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();

        $this->ensureSchemaExists();
        $this->deleteTestUsers();
    }

    protected function tearDown(): void
    {
        $this->deleteTestUsers();
        parent::tearDown();
    }

    public function testTreePermissionsForOwner(): void
    {
        $owner = $this->createUser('perm-owner@example.com', 'password');
        $tree = new Tree();
        $tree->setUser($owner);
        $tree->setName('Owner Tree');
        $tree->setCreatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($tree);
        $this->entityManager->flush();

        $this->setTokenForUser($owner);

        $permissions = static::getContainer()->get(ResourcePermissions::class)->getPermissions($tree);

        self::assertIsArray($permissions);
        self::assertContains('view', $permissions);
        self::assertContains('edit', $permissions);
        self::assertContains('delete', $permissions);
    }

    public function testTreePermissionsEmptyForNonOwner(): void
    {
        $owner = $this->createUser('perm-tree-owner2@example.com', 'password');
        $other = $this->createUser('perm-tree-other2@example.com', 'password');
        $tree = new Tree();
        $tree->setUser($owner);
        $tree->setName('Other Tree');
        $tree->setCreatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($tree);
        $this->entityManager->flush();

        $this->setTokenForUser($other);

        $permissions = static::getContainer()->get(ResourcePermissions::class)->getPermissions($tree);

        self::assertIsArray($permissions);
        self::assertEmpty($permissions);
    }

    public function testPersonPermissionsForOwner(): void
    {
        $owner = $this->createUser('perm-person-owner@example.com', 'password');
        $tree = new Tree();
        $tree->setUser($owner);
        $tree->setName('Person Tree');
        $tree->setCreatedAt(new \DateTimeImmutable());

        $person = new Person();
        $person->setTree($tree);
        $person->setFirstname('John');
        $person->setLastname('Doe');

        $this->entityManager->persist($tree);
        $this->entityManager->persist($person);
        $this->entityManager->flush();

        $this->setTokenForUser($owner);

        $permissions = static::getContainer()->get(ResourcePermissions::class)->getPermissions($person);

        self::assertIsArray($permissions);
        self::assertContains('view', $permissions);
        self::assertContains('edit', $permissions);
        self::assertContains('delete', $permissions);
    }

    public function testPersonPermissionsEmptyForNonOwner(): void
    {
        $owner = $this->createUser('perm-person-tree-owner@example.com', 'password');
        $other = $this->createUser('perm-person-other@example.com', 'password');
        $tree = new Tree();
        $tree->setUser($owner);
        $tree->setName('Person Tree 2');
        $tree->setCreatedAt(new \DateTimeImmutable());

        $person = new Person();
        $person->setTree($tree);
        $person->setFirstname('Jane');
        $person->setLastname('Doe');

        $this->entityManager->persist($tree);
        $this->entityManager->persist($person);
        $this->entityManager->flush();

        $this->setTokenForUser($other);

        $permissions = static::getContainer()->get(ResourcePermissions::class)->getPermissions($person);

        self::assertIsArray($permissions);
        self::assertEmpty($permissions);
    }

    public function testUnionPermissionsForOwner(): void
    {
        $owner = $this->createUser('perm-union-owner@example.com', 'password');
        $tree = new Tree();
        $tree->setUser($owner);
        $tree->setName('Union Tree');
        $tree->setCreatedAt(new \DateTimeImmutable());

        $person1 = new Person();
        $person1->setTree($tree);
        $person1->setFirstname('John');
        $person1->setLastname('Doe');

        $person2 = new Person();
        $person2->setTree($tree);
        $person2->setFirstname('Jane');
        $person2->setLastname('Doe');

        $union = new \App\Entity\Union();
        $union->addPerson($person1);
        $union->addPerson($person2);
        $union->setMarried(false);
        $union->setStartsAt(new \DateTimeImmutable());
        $union->setEndDayUnsure(false);
        $union->setEndMonthUnsure(false);
        $union->setEndYearUnsure(false);

        $this->entityManager->persist($tree);
        $this->entityManager->persist($person1);
        $this->entityManager->persist($person2);
        $this->entityManager->persist($union);
        $this->entityManager->flush();

        $this->setTokenForUser($owner);

        $permissions = static::getContainer()->get(ResourcePermissions::class)->getPermissions($union);

        self::assertIsArray($permissions);
        self::assertContains('view', $permissions);
        self::assertContains('edit', $permissions);
        self::assertContains('delete', $permissions);
    }

    public function testUnionPermissionsEmptyForNonOwner(): void
    {
        $owner = $this->createUser('perm-union-tree-owner@example.com', 'password');
        $other = $this->createUser('perm-union-other@example.com', 'password');
        $tree = new Tree();
        $tree->setUser($owner);
        $tree->setName('Union Tree 2');
        $tree->setCreatedAt(new \DateTimeImmutable());

        $person1 = new Person();
        $person1->setTree($tree);
        $person1->setFirstname('John');
        $person1->setLastname('Smith');

        $person2 = new Person();
        $person2->setTree($tree);
        $person2->setFirstname('Jane');
        $person2->setLastname('Smith');

        $union = new \App\Entity\Union();
        $union->addPerson($person1);
        $union->addPerson($person2);
        $union->setMarried(false);
        $union->setStartsAt(new \DateTimeImmutable());
        $union->setEndDayUnsure(false);
        $union->setEndMonthUnsure(false);
        $union->setEndYearUnsure(false);

        $this->entityManager->persist($tree);
        $this->entityManager->persist($person1);
        $this->entityManager->persist($person2);
        $this->entityManager->persist($union);
        $this->entityManager->flush();

        $this->setTokenForUser($other);

        $permissions = static::getContainer()->get(ResourcePermissions::class)->getPermissions($union);

        self::assertIsArray($permissions);
        self::assertEmpty($permissions);
    }

    private function createUser(string $email, string $plainPassword): User
    {
        $user = new User()
            ->setEmail($email)
            ->setFirstname('Perf')
            ->setLastname('User')
            ->setIsVerified(true)
        ;

        $user->setPassword(
            static::getContainer()->get(UserPasswordHasherInterface::class)->hashPassword($user, $plainPassword),
        );

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    private function setTokenForUser(User $user): void
    {
        $token = new UsernamePasswordToken(
            $user,
            'main',
            $user->getRoles(),
        );

        static::getContainer()->get(TokenStorageInterface::class)->setToken($token);
    }

    private function ensureSchemaExists(): void
    {
        $schemaTool = new SchemaTool($this->entityManager);
        $schemaTool->updateSchema($this->entityManager->getMetadataFactory()->getAllMetadata());
    }

    private function deleteTestUsers(): void
    {
        $this->entityManager->createQuery('DELETE FROM App\Entity\User user WHERE user.email LIKE :pattern')
            ->setParameter('pattern', 'perm-%')
            ->execute()
        ;
    }
}
