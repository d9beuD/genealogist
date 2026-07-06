# Services & Dependency Injection (Symfony 8.1)

Almost everything an app "does" is done by an object called a **service**, living in the **service container**. Ask for a service by type-hinting a constructor (or controller-method) argument with its class/interface — this is **autowiring**.

## Default config (already present in new apps)

```yaml
# config/services.yaml
services:
    _defaults:
        autowire: true      # injects dependencies automatically
        autoconfigure: true # registers commands, event listeners, etc.

    App\:
        resource: '../src/' # one service per class, id = FQCN
```

With this, every class in `src/` is auto-registered and autowired. Do NOT add a YAML entry just to use a class — type-hint it and you get it.

## Create and use a service

```php
// src/Service/MessageGenerator.php
namespace App\Service;

class MessageGenerator
{
    public function getHappyMessage(): string
    {
        return 'Great work! Keep going!';
    }
}
```

```php
// inject by type-hint (controller or another service)
use App\Service\MessageGenerator;

public function new(MessageGenerator $messageGenerator): Response { /* ... */ }
```

Services are created lazily and shared (same instance returned each time).

## Constructor injection (preferred)

Use promoted, private, type-hinted properties. Type-hint the interface when available.

```php
// src/Service/TwitterClient.php
namespace App\Service;

use App\Util\Rot13Transformer;

class TwitterClient
{
    public function __construct(
        private Rot13Transformer $transformer,
    ) {
    }
}
```

`php bin/console debug:autowiring` lists all type-hints you can inject.

## The `#[Autowire]` attribute — for values autowiring can't guess

```php
use Symfony\Component\DependencyInjection\Attribute\Autowire;

public function __construct(
    // a specific service (not the default for the type)
    #[Autowire(service: 'monolog.logger.request')] private LoggerInterface $logger,
    // a container parameter
    #[Autowire('%kernel.project_dir%/data')] private string $dataDir,
    #[Autowire(param: 'kernel.debug')] private bool $debug,
    // an environment variable (supports processors like 'bool:', 'int:')
    #[Autowire(env: 'SOME_ENV_VAR')] private string $apiKey,
    // an expression
    #[Autowire(expression: 'service("App\\Mail\\MailerConfiguration").getMailerMethod()')] private string $method,
) {
}
```

Use explicit YAML config ONLY when autowiring cannot resolve the argument.

## Service tags

Tags tell Symfony to register a service in a special way. With `autoconfigure: true`, tags like `twig.extension` are added automatically when the class implements the right interface — you usually do nothing.

Manual tag:

```yaml
# config/services.yaml
services:
    App\Twig\AppExtension:
        tags: ['twig.extension']
```

Auto-tag all classes implementing an interface:

```yaml
services:
    _instanceof:
        App\Security\CustomInterface:
            tags: ['app.custom_tag']
```

Or on the interface/base class itself:

```php
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.custom_tag')]
interface CustomInterface {}
```

## Factories — delegate object creation

```php
// src/Email/NewsletterManagerStaticFactory.php
namespace App\Email;

class NewsletterManagerStaticFactory
{
    public static function createNewsletterManager(): NewsletterManager
    {
        return new NewsletterManager();
    }
}
```

```yaml
# config/services.yaml
services:
    App\Email\NewsletterManager:
        # [class, static method]
        factory: ['App\Email\NewsletterManagerStaticFactory', 'createNewsletterManager']
```

## Service decoration

Wrap an existing service while keeping the same id. Inject the original with `#[AutowireDecorated]`.

```php
namespace App\Mailer;

use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;

#[AsDecorator(decorates: Mailer::class)]
class DecoratingMailer
{
    public function __construct(
        #[AutowireDecorated] private Mailer $inner,
    ) {
    }
}
```

Multiple decorators: add `priority: <int>` (higher = applied later/outermost).

## Verify (run once, don't loop)

- `php bin/console debug:autowiring [Type]`
- `php bin/console debug:container [id]`
