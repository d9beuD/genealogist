# String component + Emoji

## String component

Install: `composer require symfony/string`. Object-oriented Unicode strings. Three classes:
- `ByteString` — raw bytes (helper `b()`).
- `CodePointString` — code points.
- `UnicodeString` — grapheme clusters; the common choice (helper `u()`).

All methods are immutable (return a new object) and chainable.

### Create

```php
use Symfony\Component\String\UnicodeString;
use function Symfony\Component\String\u;   // Unicode string
use function Symfony\Component\String\b;   // byte string
use function Symfony\Component\String\s;   // auto-detects byte vs unicode

$text = u('hello');
$bytes = b('hello');

// chaining
$text = new UnicodeString('This is a déjà-vu situation.')
    ->trimEnd('.')
    ->replace('déjà-vu', 'jamais-vu')
    ->append('!');
// 'This is a jamais-vu situation!'

// random bytes
$token = ByteString::fromRandom(12);              // base58 by default
$pin   = ByteString::fromRandom(6, '0123456789'); // custom alphabet
```

### Common operations

```php
// length / width
u('नमस्ते')->length();   // 4 (graphemes)
b('नमस्ते')->length();   // 18 (bytes)

// case
u('Foo: Bar-baz.')->camel();  // 'fooBarBaz'
u('Foo: Bar-baz.')->snake();  // 'foo_bar_baz'
u('Foo: Bar-baz.')->kebab();  // 'foo-bar-baz'
u('Foo: Bar-baz.')->pascal(); // 'FooBarBaz'
u('foo bar')->upper();        // 'FOO BAR'
u('FOO Bar')->lower();        // 'foo bar'
u('foo ijssel')->title(allWords: true); // 'Foo Ijssel'

// case-insensitive ops
u('abc')->ignoreCase()->indexOf('B'); // 1

// append / prepend / ensure
u('world')->prepend('hello', ' '); // 'hello world'
u('Name')->ensureStart('get');     // 'getName'

// slice / truncate
u('Symfony is great')->slice(0, 7);   // 'Symfony'
u('Lorem Ipsum')->truncate(8, '…');   // 'Lorem I…'

// tests
u('hello world')->startsWith('hello'); // true
u('  hi  ')->collapseWhitespace();     // 'hi'
u('')->isEmpty();                      // true
```

### Slugger (URL slugs)

```php
use Symfony\Component\String\Slugger\AsciiSlugger;

$slugger = new AsciiSlugger();
$slug = $slugger->slug('Wôrķšƥáçè ~~sèťtïñğš~~'); // 'Workspace-settings'

// custom separator
$slug = $slugger->slug('Hello World', '/');

// locale + custom symbol map
$slugger = new AsciiSlugger('en', ['en' => ['%' => 'percent', '€' => 'euro']]);
$slug = $slugger->slug('10% or 5€'); // '10-percent-or-5-euro'
```

In the framework, autowire `SluggerInterface` instead of instantiating manually.

## Emoji component

Install: `composer require symfony/emoji`. Use `EmojiTransliterator`.

### Emoji to text (any language)

```php
use Symfony\Component\Emoji\EmojiTransliterator;

$transliterator = EmojiTransliterator::create('en');
$transliterator->transliterate('Menus with 🍕 or 🍝');
// 'Menus with pizza or spaghetti'

EmojiTransliterator::create('uk')->transliterate('Menus with 🍕'); // 'Menus with піца'
```

### Short codes (GitHub, GitLab, Slack)

```php
// emoji -> short code
EmojiTransliterator::create('emoji-github')->transliterate('🐢 love 🍕');
// ':turtle: love :pizza:'

// short code -> emoji
EmojiTransliterator::create('github-emoji')->transliterate(':turtle: love :pizza:');
// '🐢 love 🍕'
```

Locales: `emoji-github` / `github-emoji`, `emoji-gitlab` / `gitlab-emoji`, `emoji-slack` / `slack-emoji`. Use `text-emoji` to convert short codes from any of these services to emoji when the source is unknown.

STOP once the helper call (`u()`, `b()`, `->slug()`, `transliterate()`) is written.
