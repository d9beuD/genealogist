# Translation / i18n

Symfony 8.1. Install: `composer require symfony/translation`. Translation files live in `translations/` named `DOMAIN.LOCALE.FORMAT` (e.g. `messages.fr.yaml`).

## 1. Translate in a controller/service

Type-hint `TranslatorInterface` and call `trans()`.

```php
use Symfony\Contracts\Translation\TranslatorInterface;

public function index(TranslatorInterface $translator): Response
{
    $translated = $translator->trans('Symfony is great');
    // optionally choose the domain: ->trans('...', domain: 'messages')

    // ...
}
```

Symfony translates based on the user's locale. Done when the `trans()` call is written.

## 2. Translation files

```yaml
# translations/messages.fr.yaml
Symfony is great: Symfony est génial
say_hello: 'Hello %name%!'
```

Equivalent PHP format:

```php
// translations/messages.fr.php
return [
    'Symfony is great' => 'Symfony est génial',
    'say_hello' => 'Hello %name%!',
];
```

You may use the real English text as the key, or a keyword like `say_hello`.

## 3. Placeholders

Pass values as the second argument to `trans()`. The `%name%` wrapping is a convention (PHP `strtr`):

```php
$translated = $translator->trans('say_hello', ['%name%' => 'Fabien']);
// 'Hello Fabien!'
```

## 4. Pluralization / gender (ICU MessageFormat)

For count- or gender-based messages, use ICU syntax. ICU uses `{name}` placeholders and requires the `+intl-icu` suffix in the filename.

```yaml
# translations/messages+intl-icu.en.yaml
num_apples: >-
    {count, plural,
        =0 {There are no apples}
        one {There is one apple}
        other {There are # apples}
    }
```

```php
$translator->trans('num_apples', ['count' => 5]);
// 'There are 5 apples'
```

## 5. TranslatableMessage (deferred translation)

Use when text is created away from the translator (enums, services, value objects). It stores message id + parameters + domain and is translated later (e.g. in Twig).

```php
use Symfony\Component\Translation\TranslatableMessage;

// default domain ("messages")
$message = new TranslatableMessage('notification.welcome');

// with parameters and a custom domain
$status = new TranslatableMessage(
    'order.status',
    ['%status%' => $order->getStatus()],
    'store'
);
```

In Twig, pass to the `trans` filter:

```twig
<h1>{{ message|trans }}</h1>
<p>{{ status|trans }}</p>
```

`t()` shortcut (less boilerplate, works in PHP and Twig):

```php
use function Symfony\Component\Translation\t;

$message = t('notification.welcome');
$status  = t('order.status', ['%status%' => $order->getStatus()], 'store');
```

Prefer `TranslatableMessage` / `t()` over plain strings in enums and services: it carries the domain/parameters and is detected by the `translation:extract` command.

STOP once the `trans()` call or the `TranslatableMessage` / `t()` instance is written.
