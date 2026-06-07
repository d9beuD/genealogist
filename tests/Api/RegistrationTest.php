<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationTest extends WebTestCase
{
    private const string EMAIL = 'registered-user@example.com';

    private const string DUPLICATE_EMAIL = 'duplicate-user@example.com';

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

    public function testRegisterCreatesUserWithHashedPassword(): void
    {
        $this->client->jsonRequest('POST', '/api/register', $this->validPayload());

        self::assertResponseStatusCodeSame(201);
        self::assertResponseHeaderSame('content-type', 'application/json; charset=utf-8');

        $response = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame(self::EMAIL, $response['email']);
        self::assertSame('Registered', $response['firstname']);
        self::assertSame('User', $response['lastname']);
        self::assertArrayNotHasKey('plainPassword', $response);
        self::assertArrayNotHasKey('password', $response);

        $user = $this->findUser(self::EMAIL);

        self::assertNotNull($user);
        self::assertSame('Registered', $user->getFirstname());
        self::assertSame('User', $user->getLastname());
        self::assertFalse($user->isVerified());
        self::assertNotSame('valid-password', $user->getPassword());
        self::assertTrue(static::getContainer()->get(UserPasswordHasherInterface::class)->isPasswordValid($user, 'valid-password'));
    }

    public function testRegisteredUserCanLoginImmediately(): void
    {
        $this->client->jsonRequest('POST', '/api/register', $this->validPayload());
        self::assertResponseStatusCodeSame(201);

        $this->client->jsonRequest('POST', '/api/auth', [
            'email' => self::EMAIL,
            'password' => 'valid-password',
        ]);

        self::assertResponseIsSuccessful();
    }

    public function testGetRegisterReturnsEmptyCollectionForApiDiscovery(): void
    {
        $this->client->jsonRequest('GET', '/api/register');

        self::assertResponseStatusCodeSame(200);
        self::assertSame([], json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR));
    }

    public function testRegisterRejectsInvalidEmail(): void
    {
        $payload = $this->validPayload();
        $payload['email'] = 'not-an-email';

        $this->client->jsonRequest('POST', '/api/register', $payload);

        self::assertResponseStatusCodeSame(422);
    }

    public function testRegisterValidationErrorsAreTranslatedToFrench(): void
    {
        $payload = $this->validPayload();
        $payload['email'] = 'not-an-email';
        $this->client->setServerParameter('HTTP_ACCEPT_LANGUAGE', 'fr');

        $this->client->jsonRequest('POST', '/api/register', $payload);

        self::assertResponseStatusCodeSame(422);
        self::assertSame('Veuillez saisir une adresse email valide.', $this->firstViolationMessage());
    }

    public function testRegisterRejectsShortPassword(): void
    {
        $payload = $this->validPayload();
        $payload['plainPassword'] = 'short';

        $this->client->jsonRequest('POST', '/api/register', $payload);

        self::assertResponseStatusCodeSame(422);
    }

    public function testRegisterRejectsDuplicateEmail(): void
    {
        $this->createUser(self::DUPLICATE_EMAIL, 'password');

        $payload = $this->validPayload();
        $payload['email'] = self::DUPLICATE_EMAIL;

        $this->client->jsonRequest('POST', '/api/register', $payload);

        self::assertResponseStatusCodeSame(422);
    }

    public function testDuplicateEmailErrorIsTranslatedToFrench(): void
    {
        $this->createUser(self::DUPLICATE_EMAIL, 'password');

        $payload = $this->validPayload();
        $payload['email'] = self::DUPLICATE_EMAIL;
        $this->client->setServerParameter('HTTP_ACCEPT_LANGUAGE', 'fr');

        $this->client->jsonRequest('POST', '/api/register', $payload);

        self::assertResponseStatusCodeSame(422);
        self::assertSame('Un compte existe déjà avec cette adresse email.', $this->firstViolationMessage());
    }

    /**
     * @return array{email: string, firstname: string, lastname: string, plainPassword: string}
     */
    private function validPayload(): array
    {
        return [
            'email' => self::EMAIL,
            'firstname' => 'Registered',
            'lastname' => 'User',
            'plainPassword' => 'valid-password',
        ];
    }

    private function createUser(string $email, string $plainPassword): void
    {
        $user = new User()
            ->setEmail($email)
            ->setFirstname('Existing')
            ->setLastname('User')
            ->setIsVerified(true)
        ;

        $user->setPassword(
            static::getContainer()->get(UserPasswordHasherInterface::class)->hashPassword($user, $plainPassword),
        );

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    private function firstViolationMessage(): string
    {
        $response = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

        return $response['violations'][0]['message'];
    }

    private function findUser(string $email): ?User
    {
        return $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
    }

    private function ensureSchemaExists(): void
    {
        $schemaTool = new SchemaTool($this->entityManager);
        $schemaTool->updateSchema($this->entityManager->getMetadataFactory()->getAllMetadata());
    }

    private function deleteTestUsers(): void
    {
        $this->entityManager->createQuery('DELETE FROM App\Entity\User user WHERE user.email IN (:emails)')
            ->setParameter('emails', [self::EMAIL, self::DUPLICATE_EMAIL])
            ->execute()
        ;
    }
}
