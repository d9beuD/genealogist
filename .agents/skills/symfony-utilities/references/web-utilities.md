# Web utilities: HTML sanitizer, session/flash, WebLink

## HTML Sanitizer

Install: `composer require symfony/html-sanitizer`. Removes unsafe tags/attributes from untrusted HTML (rebuilds HTML from allowed elements only). Always sanitize user-supplied HTML before storing or rendering it.

Autowire `HtmlSanitizerInterface` (the `html_sanitizer` service) and call `sanitize()`:

```php
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

public function createAction(HtmlSanitizerInterface $htmlSanitizer, Request $request): Response
{
    $unsafeContents = $request->request->getString('post_contents');

    $safeContents = $htmlSanitizer->sanitize($unsafeContents);
    // store/render $safeContents
}
```

The default config allows all "safe" elements/attributes. Sanitize for a specific context with `sanitizeFor()`:

```php
$safe = $htmlSanitizer->sanitizeFor('body', $userInput);     // body context
$safe = $htmlSanitizer->sanitizeFor('textarea', $userInput);
```

Standalone (custom config):

```php
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

$htmlSanitizer = new HtmlSanitizer(
    new HtmlSanitizerConfig()->allowSafeElements()
);
$safe = $htmlSanitizer->sanitize($unsafeContents);
```

In Forms, enable on `TextType` / `TextareaType` with the `'sanitize_html' => true` option.

Done when `$htmlSanitizer->sanitize($input)` is written.

## Session & Flash messages

The session comes from the `Request` or `RequestStack`. Inject `RequestStack` in services:

```php
use Symfony\Component\HttpFoundation\RequestStack;

public function __construct(private RequestStack $requestStack) {}

public function action(): void
{
    $session = $this->requestStack->getSession();

    // write
    $session->set('attribute-name', 'attribute-value');

    // read (2nd arg is the default when missing)
    $foo = $session->get('foo');
    $filters = $session->get('filters', []);
}
```

In a controller you can also do `$session = $request->getSession();`.

Note: the session starts automatically the moment you read/write it, which sets a cookie. Avoid touching it for anonymous users if you don't need it.

### Flash messages (shown exactly once)

In a controller, use `addFlash()`:

```php
$this->addFlash('notice', 'Your changes were saved!');
// equivalent to $request->getSession()->getFlashBag()->add('notice', '...')
```

Display them in Twig:

```twig
{% for message in app.flashes('notice') %}
    <div class="flash-notice">{{ message }}</div>
{% endfor %}
```

Read in PHP via the flash bag (`get()` removes them, `peek()` keeps them):

```php
$flashes = $session->getFlashBag();
foreach ($flashes->get('warning', []) as $message) { /* ... */ }
```

Done when `$session->set()/get()` or `$this->addFlash()` is written.

## WebLink (asset preloading / resource hints)

Install: `composer require symfony/web-link`. Adds `Link` HTTP headers so the browser preloads resources. Use the `preload()` Twig function (the `as` attribute is required):

```twig
{# templates/base.html.twig #}
<link rel="preload" href="{{ preload('/fonts/myfont.woff2', {as: 'font'}) }}">

<link rel="preload"
      href="{{ preload(asset('build/app.css'), {as: 'style'}) }}"
      as="style">

{# extra attributes #}
<link rel="preload" href="{{ preload('/fonts/myfont.woff2', {as: 'font', type: 'font/woff2', crossorigin: 'anonymous'}) }}">
```

This emits e.g. `Link: </fonts/myfont.woff2>; rel="preload"; as="font"`. When supported, links are also sent as HTTP 103 Early Hints automatically.

Done when the `preload()` function is added to the template.

STOP once the relevant call is written; do not run the app to verify.
