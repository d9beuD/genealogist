# Messenger (async / queues / message bus)

Symfony 8.1. Steps: define message → handler → route to transport → consume.

## 1. Message (plain DTO)
```php
// src/Message/SmsNotification.php
namespace App\Message;

class SmsNotification
{
    public function __construct(
        private string $content,
    ) {
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
```

## 2. Handler
Use `#[AsMessageHandler]`. Autoconfiguration matches the handler by the type-hinted message in `__invoke()`.
```php
// src/MessageHandler/SmsNotificationHandler.php
namespace App\MessageHandler;

use App\Message\SmsNotification;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SmsNotificationHandler
{
    public function __invoke(SmsNotification $message)
    {
        // ... do the work, e.g. send an SMS
    }
}
```
List configured handlers: `php bin/console debug:messenger`.

## 3. Dispatch
Inject `MessageBusInterface` (the default bus).
```php
use Symfony\Component\Messenger\MessageBusInterface;

public function index(MessageBusInterface $bus): Response
{
    $bus->dispatch(new SmsNotification('Look! I created a message!'));
    // ...
}
```
By default messages are handled synchronously on dispatch. To go async, route to a transport (step 4).

## 4. Transports (async)
Set the DSN in `.env`:
```
# MESSENGER_TRANSPORT_DSN=doctrine://default
# MESSENGER_TRANSPORT_DSN=amqp://guest:guest@localhost:5672/%2f/messages
# MESSENGER_TRANSPORT_DSN=redis://localhost:6379/messages
```
Configure transport + routing in `config/packages/messenger.yaml`:
```yaml
framework:
    messenger:
        transports:
            async: "%env(MESSENGER_TRANSPORT_DSN)%"
        routing:
            'App\Message\SmsNotification': async
```
Routing rules apply to the class AND its parents/interfaces. You can route a message to multiple transports with a list: `['async', 'audit']`.

## 5. Consume (worker)
```
php bin/console messenger:consume async
php bin/console messenger:consume async -vv
php bin/console messenger:consume --all
php bin/console messenger:consume async --limit=10
```
DONE when a dispatched message is processed by the worker.

## Stamps & Envelope
Wrap a message in an `Envelope` to attach stamps.
```php
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Messenger\Stamp\PriorityStamp;

$bus->dispatch((new Envelope($message))->with(
    new PriorityStamp(255),
    new DelayStamp(5000), // delay in milliseconds
));
```
Override the transport at runtime with `TransportNamesStamp`.

## Retries & failures
Configure per transport:
```yaml
framework:
    messenger:
        failure_transport: failed
        transports:
            async:
                dsn: '%env(MESSENGER_TRANSPORT_DSN)%'
                retry_strategy:
                    max_retries: 3
                    # delay (ms), multiplier, max_delay also configurable
            failed: 'doctrine://default?queue_name=failed'
```
Inspect / retry failed messages:
```
php bin/console messenger:failed:show
php bin/console messenger:failed:show --class-filter='App\Message\MyMessage'
php bin/console messenger:failed:retry -vv
php bin/console messenger:failed:remove 20
```

## Doctrine entities in messages
Pass the entity's primary key, NOT the entity object. Re-query a fresh object inside the handler.

## Anti-loop
- One message + one handler per pass. Stop once `messenger:consume` processes it.
- If `messenger.yaml` already has the transport/routing, reuse it. Do not duplicate keys.
