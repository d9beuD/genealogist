---
name: symfony-utilities
description: >-
  Use for Symfony 8.1 utility components and helper tasks. Triggers: create or
  test a console command (bin/console, #[AsCommand], SymfonyStyle output, command
  arguments/options, verbosity); send email with Mailer (Email, TemplatedEmail,
  attachments, Twig emails); send notifications with Notifier (SMS via Texter,
  chat/Slack via Chatter, multi-channel Notification); translation and i18n
  (translator, trans(), TranslatableMessage, placeholders, pluralization);
  manipulate strings with the String component (u(), b(), slugger, case
  conversion, truncate); transliterate emoji; sanitize untrusted HTML
  (HtmlSanitizer); read/write sessions and flash messages; preload assets with
  WebLink. Reach for this skill whenever the task is "make a command", "send an
  email", "send an SMS or Slack message", "translate text", "slugify", "clean
  user HTML", or "store something in the session".
---

Build Symfony 8.1 utility code (console, mailer, notifier, translation, strings, emoji, HTML sanitizer, session, web link) from real component APIs.

This file is a ROUTER. Pick ONE task, open ONE reference file, write the code, stop. Do not read multiple references.

## Use this skill when

- Creating, configuring, or testing a console command (`bin/console`, `#[AsCommand]`).
- Sending email (Mailer `Email` / `TemplatedEmail`, attachments, Twig templates).
- Sending notifications: SMS (`Texter`), chat/Slack (`Chatter`), or multi-channel `Notification`.
- Translating text / i18n: `trans()`, `TranslatableMessage`, placeholders, plurals.
- Working with strings: `u()`, `b()`, slugger, case conversion, truncate.
- Transliterating emoji or converting emoji short codes.
- Sanitizing untrusted/user-supplied HTML.
- Reading/writing the session or flash messages.
- Preloading assets / resource hints (WebLink).

## Do not use this skill when

- Defining controllers, routing, or HTTP responses (use the basics skill).
- Doctrine entities, forms, validation, or security (use the matching skill).
- Messenger queues, caching, deployment, or profiling (use other skills).
- Front-end assets bundling (AssetMapper) beyond `preload()` (use the frontend skill).

## Instructions

Follow these steps in order. Do not loop.

1. Identify the single task from the user request. Match it to exactly ONE reference file using the table in "Reference files".
2. Open that ONE reference file. Do not open others.
3. Copy the closest code template from that file. Keep the exact namespaces and class names shown there.
4. Adapt names, fields, and values to the user request. Change nothing else.
5. Confirm PHP 8.2+ attribute syntax (`#[AsCommand]`, `#[Argument]`, `#[Option]`, `#[Route]`) where the reference uses it.
6. STOP when the deliverable is done. Done criteria, per task:
   - Console command: class created with `#[AsCommand]` and `__invoke(): int` returning `Command::SUCCESS`.
   - Email: `$mailer->send($email);` line written.
   - SMS: `$texter->send($sms);` written. Chat: `$chatter->send($message);` written. Notification: `$notifier->send($notification, $recipient);` written.
   - Translation: `trans()` call or `TranslatableMessage` instance written.
   - String/slug/emoji: the helper call (`u()`, `b()`, `->slug()`, `transliterate()`) written.
   - HTML sanitizer: `$htmlSanitizer->sanitize($input)` written.
   - Session/flash: `$session->set()/get()` or `$this->addFlash()` written.
   - WebLink: `preload()` Twig function added.
7. Do NOT run `bin/console`, send a real email, or call external transports to "verify". Writing the correct code is the end state.
8. If the request spans two areas (e.g. a command that sends email), finish area one fully, then return to step 1 for area two. Never read two references at once.

## Reference files

- `references/console-commands.md` — open when creating, structuring, styling (`SymfonyStyle`), giving input (`#[Argument]`, `#[Option]`), setting verbosity, or testing a console command.
- `references/mailer.md` — open when sending email: `Email`, `TemplatedEmail`, attachments, embedded images, Twig email templates, async sending.
- `references/notifier.md` — open when sending SMS (`Texter`/`SmsMessage`), chat/Slack (`Chatter`/`ChatMessage`), or a multi-channel `Notification` to a `Recipient`.
- `references/translation.md` — open when translating: `trans()`, placeholders, `TranslatableMessage`, `t()` shortcut, ICU pluralization, translation files.
- `references/string-misc.md` — open when manipulating strings (`u()`/`b()`, case, truncate, slug) or transliterating emoji.
- `references/web-utilities.md` — open when sanitizing untrusted HTML, reading/writing the session and flash messages, or preloading assets with WebLink.
