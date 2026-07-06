# Events & Event Listeners (Symfony 8.1)

Symfony dispatches kernel events while handling each HTTP request (e.g. `kernel.request`, `kernel.controller`, `kernel.response`, `kernel.exception`). Listen to them, or dispatch your own events. See the events reference for each event's object type.

## Event listener with attribute (preferred)

```php
// src/EventListener/ExceptionListener.php
namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

#[AsEventListener]
final class ExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $response = new Response('Error: '.$exception->getMessage());
        $response->headers->set('Content-Type', 'text/plain; charset=utf-8');
        $event->setResponse($response);
    }
}
```

With `autoconfigure: true`, the attribute is enough — no YAML needed.

Method resolution: if the tag/attribute sets `method`, that method is called; else `__invoke()`; else `on<EventName>()` when the `event` is known. Add `priority` (higher = earlier).

```php
#[AsEventListener(event: 'kernel.exception', method: 'onKernelException', priority: 10)]
```

## Event listener with tag (YAML alternative)

```yaml
# config/services.yaml
services:
    App\EventListener\ExceptionListener:
        tags: [kernel.event_listener]
```

Optional tag attributes: `event`, `method`, `priority`.

## Event subscriber (one class, many events)

Use when a class handles several events; subscribers declare their events themselves.

```php
// src/EventSubscriber/ExceptionSubscriber.php
namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            // event => [ [method, priority], ... ]
            ExceptionEvent::class => [
                ['processException', 10],
                ['logException', 0],
                ['notifyException', -10],
            ],
        ];
    }

    public function processException(ExceptionEvent $event): void { /* ... */ }
    public function logException(ExceptionEvent $event): void { /* ... */ }
    public function notifyException(ExceptionEvent $event): void { /* ... */ }
}
```

`autoconfigure: true` registers any `EventSubscriberInterface` automatically.

## Dispatch a custom event

```php
// src/Event/OrderPlacedEvent.php
namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class OrderPlacedEvent extends Event
{
    public function __construct(public readonly int $orderId) {}
}
```

```php
use Psr\EventDispatcher\EventDispatcherInterface;
use App\Event\OrderPlacedEvent;

public function __construct(private EventDispatcherInterface $dispatcher) {}

public function place(int $orderId): void
{
    $this->dispatcher->dispatch(new OrderPlacedEvent($orderId));
}
```

Listen to it by type-hinting `OrderPlacedEvent` in a listener/subscriber.

## Verify (run once)

- `php bin/console debug:event-dispatcher [event]`
