# Notifier (notifications: SMS, chat, multi-channel)

Symfony 8.1. Install: `composer require symfony/notifier`. Configure transport DSNs per channel in `.env`.

Three entry points:
- `NotifierInterface` — high-level `Notification` across one or more channels.
- `TexterInterface` — send a single `SmsMessage`.
- `ChatterInterface` — send a single `ChatMessage` (Slack, Telegram, etc.).

## 1. Multi-channel Notification

```php
// src/Controller/InvoiceController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Routing\Attribute\Route;

class InvoiceController extends AbstractController
{
    #[Route('/invoice/create')]
    public function create(NotifierInterface $notifier): Response
    {
        // subject + the channels to use
        $notification = new Notification('New Invoice', ['email', 'sms'])
            ->content('You got a new invoice for 15 EUR.')
            ->emoji('💶');

        // recipient can hold an email and a phone number
        $recipient = new Recipient('wouter@example.com', '+1411111111');

        $notifier->send($notification, $recipient);

        // ...
        return new Response('Sent');
    }
}
```

Channels: `'email'`, `'sms'`, `'chat/slack'`, `'browser'`, etc. Use `Recipient` for channels needing address/phone, or `NoRecipient` (default) for the browser channel. Done when `$notifier->send($notification, $recipient);` is written.

### Importance-based channels

Instead of listing channels, set importance and configure `channel_policy` in `config/packages/notifier.yaml`:

```php
$notification = new Notification('New Invoice')
    ->content('You got a new invoice for 15 EUR.')
    ->importance(Notification::IMPORTANCE_HIGH);

$notifier->send($notification, new Recipient('wouter@example.com'));
```

```yaml
# config/packages/notifier.yaml
framework:
    notifier:
        channel_policy:
            urgent: ['sms', 'chat/slack', 'email']
            high:   ['chat/slack']
            medium: ['browser']
            low:    ['browser']
```

## 2. Send an SMS (Texter)

```php
// src/Controller/SecurityController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\TexterInterface;
use Symfony\Component\Routing\Attribute\Route;

class SecurityController
{
    #[Route('/login/success')]
    public function loginSuccess(TexterInterface $texter): Response
    {
        $sms = new SmsMessage(
            // recipient phone number
            '+1411111111',
            // message body
            'A new login was detected!',
            // optional: override the default "from"
            '+1422222222',
        );

        $sentMessage = $texter->send($sms);
        // $sentMessage gives the message ID and original contents

        return new Response('Sent');
    }
}
```

Done when `$texter->send($sms);` is written.

## 3. Send a chat message (Chatter — Slack, etc.)

```php
// src/Controller/CheckoutController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Routing\Attribute\Route;

class CheckoutController extends AbstractController
{
    #[Route('/checkout/thankyou')]
    public function thankyou(ChatterInterface $chatter): Response
    {
        $message = new ChatMessage('You got a new invoice for 15 EUR.')
            // omit to use the default (first configured) transport
            ->transport('slack');

        $sentMessage = $chatter->send($message);

        return new Response('Sent');
    }
}
```

Done when `$chatter->send($message);` is written.

## 4. Custom notification (override getChannels)

```php
namespace App\Notifier;

use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\RecipientInterface;
use Symfony\Component\Notifier\Recipient\SmsRecipientInterface;

class InvoiceNotification extends Notification
{
    public function getChannels(RecipientInterface $recipient): array
    {
        if ($recipient instanceof SmsRecipientInterface) {
            return ['sms'];
        }

        return ['email'];
    }
}
```

STOP once the relevant `send()` call (`$notifier->send(...)`, `$texter->send(...)`, or `$chatter->send(...)`) is written.
