# Scheduler + Mercure + Webhook

Three components. Pick the one the task needs.

---

# Scheduler (recurring / cron tasks)

Symfony 8.1. Built on Messenger.

## Schedule provider
A class with `#[AsSchedule]` implementing `ScheduleProviderInterface`.
```php
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

#[AsSchedule]
class SaleTaskProvider implements ScheduleProviderInterface
{
    public function getSchedule(): Schedule
    {
        return (new Schedule())->add(
            RecurringMessage::every('10 seconds', new SendSaleReport()),
            RecurringMessage::cron('0 8 * * 1', new WeeklyReport()),
        );
    }
}
```
- Schedule name defaults to `default`; transport is `scheduler_<name>` (e.g. `scheduler_default`).
- The dispatched messages are normal Messenger messages: give each a `#[AsMessageHandler]`.

## Recurring messages
```php
RecurringMessage::every('10 seconds', new Message());
RecurringMessage::every('1 day', new Message(), $from, $until); // optional bounds
RecurringMessage::cron('* * * * *', new Message());
RecurringMessage::cron('* * * * *', new Message(), new \DateTimeZone('Africa/Malabo'));
RecurringMessage::cron('@daily', new Message());     // also #midnight, #hourly
```

## Run
```
php bin/console messenger:consume scheduler_default
php bin/console messenger:consume scheduler_.*
```
DONE when `getSchedule()` returns a `Schedule` and the transport is consumed.

---

# Mercure (realtime / SSE)

Symfony 8.1. Push updates to subscribed browsers.

`config/packages/mercure.yaml`:
```yaml
mercure:
    hubs:
        default:
            url: '%env(MERCURE_URL)%'
```
Set `MERCURE_URL` in `.env`. Publish from a controller/service:
```php
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

public function publish(HubInterface $hub): Response
{
    $update = new Update(
        'https://example.com/books/1',          // topic
        json_encode(['status' => 'OutOfStock']) // data
    );

    $hub->publish($update);

    return new Response('published!');
}
```
DONE when `$hub->publish()` returns without error.

---

# Webhook + RemoteEvent (inbound webhooks)

Symfony 8.1. Receive external HTTP callbacks, verify them, turn them into `RemoteEvent`, then consume.

## 1. Route the webhook
`config/packages/webhook.yaml`:
```yaml
framework:
    webhook:
        routing:
            acme_webhook:   # maps to /webhook/acme_webhook
                service: App\Webhook\AcmeWebhookRequestParser
                secret: '%env(ACME_WEBHOOK_SECRET)%'
```

## 2. Request parser
Extend `AbstractRequestParser`. Validate the request and return a `RemoteEvent`.
```php
namespace App\Webhook;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher\ChainRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcher\IsJsonRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcher\MethodRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcher\RequestMatcherInterface;
use Symfony\Component\RemoteEvent\RemoteEvent;
use Symfony\Component\Webhook\Client\AbstractRequestParser;

final class AcmeWebhookRequestParser extends AbstractRequestParser
{
    protected function getRequestMatcher(): RequestMatcherInterface
    {
        return new ChainRequestMatcher([
            new IsJsonRequestMatcher(),
            new MethodRequestMatcher('POST'),
        ]);
    }

    protected function doParse(Request $request, #[\SensitiveParameter] string $secret): ?RemoteEvent
    {
        // validate signature with $secret (typically HMAC-SHA256);
        // throw RejectWebhookException on invalid requests
        $payload = $request->toArray();

        return new RemoteEvent($payload['event_type'], $payload['event_id'], $payload);
    }
}
```

## 3. Consumer
Name MUST match the routing name.
```php
use Symfony\Component\RemoteEvent\Attribute\AsRemoteEventConsumer;
use Symfony\Component\RemoteEvent\Consumer\ConsumerInterface;
use Symfony\Component\RemoteEvent\RemoteEvent;

#[AsRemoteEventConsumer('acme_webhook')]
final class AcmeWebhookConsumer implements ConsumerInterface
{
    public function consume(RemoteEvent $event): void
    {
        // application logic using $event->getPayload()
    }
}
```
DONE when the parser returns a `RemoteEvent` and the consumer is invoked.

## Anti-loop
- One component per pass (schedule OR mercure OR webhook).
- If config (mercure.yaml / webhook.yaml) already exists, reuse it.
- For webhooks: parser name, routing name, and `#[AsRemoteEventConsumer]` name must all match. Stop once the consumer fires.
