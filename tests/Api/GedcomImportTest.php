<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\Entity\GedcomMetadata;
use App\Entity\Person;
use App\Entity\Tree;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class GedcomImportTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient([], ['HTTPS' => 'on']);
        $this->entityManager = self::getContainer()->get('doctrine')->getManager();
        new SchemaTool($this->entityManager)->updateSchema($this->entityManager->getMetadataFactory()->getAllMetadata());
        $this->cleanup();
    }

    protected function tearDown(): void
    {
        $this->cleanup();
        parent::tearDown();
    }

    public function testImportsFixtureIntoNewTreeAndReimportsByFilename(): void
    {
        $this->createUser();
        $this->login();
        $this->upload($this->fixture('family-5.5.ged'), ['treeName' => 'Imported family']);

        self::assertResponseStatusCodeSame(201);
        $response = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame('5.5', $response['format']);
        self::assertSame(2, $response['created']['people']);
        self::assertSame(1, $response['created']['unions']);
        self::assertSame('Imported family', $response['tree']['name']);

        $tree = $this->entityManager->getRepository(Tree::class)->find($response['tree']['id']);
        self::assertInstanceOf(Tree::class, $tree);
        self::assertCount(2, $this->entityManager->getRepository(Person::class)->findBy(['tree' => $tree]));
        self::assertGreaterThanOrEqual(2, \count($this->entityManager->getRepository(GedcomMetadata::class)->findAll()));

        $path = tempnam(sys_get_temp_dir(), 'gedcom-reimport-');
        self::assertNotFalse($path);
        file_put_contents($path, str_replace('John /Doe/', 'Johnny /Doe/', (string) file_get_contents(__DIR__ . '/../Fixtures/Gedcom/family-5.5.ged')));
        $this->upload(new UploadedFile($path, 'family-5.5.ged', 'application/octet-stream', null, true), ['treeId' => (string) $tree->getId()]);

        self::assertResponseStatusCodeSame(201);
        $updated = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame(2, $updated['updated']['people']);
        $this->entityManager->clear();
        $tree = $this->entityManager->getRepository(Tree::class)->find($tree->getId());
        self::assertInstanceOf(Tree::class, $tree);
        self::assertCount(2, $this->entityManager->getRepository(Person::class)->findBy(['tree' => $tree]));
        self::assertNotNull($this->entityManager->getRepository(Person::class)->findOneBy(['tree' => $tree, 'firstname' => 'Johnny']));
    }

    public function testImportRequiresCsrfToken(): void
    {
        $this->createUser();
        $this->client->jsonRequest('POST', '/api/auth', ['email' => 'gedcom@example.com', 'password' => 'password']);
        self::assertResponseIsSuccessful();

        $this->upload($this->fixture('family-7.0.ged'));
        self::assertResponseStatusCodeSame(403);
    }

    /**
     * @param array<string, string>|array<string, decimal-int-string>|string[] $parameters
     */
    private function upload(UploadedFile $file, array $parameters = []): void
    {
        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_POST, '/api/trees/import', $parameters, ['file' => $file], [
            'HTTP_ACCEPT' => 'application/json',
            'CONTENT_TYPE' => 'multipart/form-data',
        ]);
    }

    private function fixture(string $name): UploadedFile
    {
        return new UploadedFile(__DIR__ . '/../Fixtures/Gedcom/' . $name, $name, 'application/octet-stream', null, true);
    }

    private function createUser(): void
    {
        $user = new User()->setEmail('gedcom@example.com')->setFirstname('Gedcom')->setLastname('User')->setIsVerified(true);
        $user->setPassword(self::getContainer()->get(UserPasswordHasherInterface::class)->hashPassword($user, 'password'));

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    private function login(): void
    {
        $this->client->jsonRequest('POST', '/api/auth', ['email' => 'gedcom@example.com', 'password' => 'password']);
        self::assertResponseIsSuccessful();
        $csrf = $this->responseCookie('csrf_token');
        self::assertNotNull($csrf);
        $this->client->setServerParameter('HTTP_X_CSRF_TOKEN', $csrf->getValue());
    }

    private function responseCookie(string $name): ?Cookie
    {
        foreach ($this->client->getResponse()->headers->getCookies() as $cookie) {
            if ($name === $cookie->getName()) {
                return $cookie;
            }
        }

        return null;
    }

    private function cleanup(): void
    {
        $this->entityManager->createQuery('DELETE FROM App\\Entity\\User user WHERE user.email = :email')->setParameter('email', 'gedcom@example.com')->execute();
    }
}
