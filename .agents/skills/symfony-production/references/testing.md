# Testing with PHPUnit (Symfony 8.1)

Install (dev): `composer require --dev symfony/test-pack`.
Run tests (ONCE per attempt; fix failures, re-run ONCE; stop after green or 2 attempts):
```bash
php bin/phpunit               # all tests
php bin/phpunit tests/Form    # a directory
php bin/phpunit tests/Form/UserTypeTest.php
```
Config: `phpunit.dist.xml`. Tests live in `tests/`, classes end in `Test`. Mirror `src/` layout. Do NOT clear caches in a loop while tests fail; read the assertion message.

## Unit tests
Plain PHPUnit `TestCase`. No kernel needed. Test one class in isolation.

## Integration tests — KernelTestCase
Boot the kernel and fetch services from the test container:
```php
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class NewsletterGeneratorTest extends KernelTestCase
{
    public function testSomething(): void
    {
        self::bootKernel();
        $container = static::getContainer(); // special test container (also gives private services)
        // $service = $container->get(SomeService::class);
    }
}
```
Kernel runs in the `test` env. Configure via `config/packages/test/` or `when@test`. `KERNEL_CLASS=App\Kernel` in `.env.test`.

## Application/functional tests — WebTestCase
```php
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PostControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Hello World');
        $this->assertCount(4, $crawler->filter('.comment'));
    }
}
```

### Common assertions
- Response: `assertResponseIsSuccessful()`, `assertResponseStatusCodeSame(int)`, `assertResponseRedirects(?location, ?code)`, `assertResponseHasHeader(name)`, `assertResponseHeaderSame(name, value)`, `assertResponseHasCookie(name)`, `assertResponseIsUnprocessable()` (422).
- Request: `assertRouteSame('route', [params])`, `assertSessionHasFlashMessage(type, msg)` (Symfony 8.1).
- DOM: `assertSelectorTextContains(selector, text)`.

### Login a user (no real auth flow)
```php
$user = static::getContainer()->get(UserRepository::class)->findOneByEmail('john@example.com');
$client->loginUser($user);            // optional 2nd arg: firewall name
$client->request('GET', '/profile');
$this->assertResponseIsSuccessful();
```
`loginUser()` does not work with stateless firewalls.

## Database tests
Test repositories against a real DB connection, not mocks (mocking is discouraged). Unit-test pure logic by injecting a mocked `EntityManager`/`EntityRepository` only when unavoidable:
```php
$repo = $this->createMock(EntityRepository::class);
$repo->method('find')->willReturn($employee);
$em = $this->createMock(EntityManager::class);
$em->method('getRepository')->willReturn($repo);
```

## Profiler in tests
Profiler is enabled but data collection is OFF by default in `test`:
```yaml
when@test:
    framework:
        profiler: { enabled: true, collect: false }
```
Enable collection for one test: `$client->enableProfiler();` then `$profile = $client->getProfile();` (assert DB calls, time, etc.).

## Smoke test pattern (best practice)
Use a data provider to assert every URL returns success. Hard-code raw URLs (not generated from routes) so route changes surface as failures:
```php
#[DataProvider('urlProvider')]
public function testPageIsSuccessful($url): void
{
    $client = self::createClient();
    $client->request('GET', $url);
    $this->assertResponseIsSuccessful();
}
public static function urlProvider(): \Generator { yield ['/']; yield ['/posts']; }
```

## Done criteria
- `php bin/phpunit` exits 0 (green). Stop. Do not re-run repeatedly.

## Repo note
Tests run with `php bin/phpunit` (here: `docker compose exec apache php bin/phpunit`). Quality scripts: `composer analyse`, `composer cs:check`, `composer cs:fix`, `composer rector`, `composer lint`.
