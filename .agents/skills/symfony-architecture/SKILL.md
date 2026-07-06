---
name: symfony-architecture
description: Build and wire the core architecture of a Symfony 8.1 app. Use this whenever you work with the service container, dependency injection, autowiring, service tags, factories, service decoration, the event dispatcher (listeners/subscribers), bundles, the kernel, or app configuration (config/packages, environment variables, secrets, parameters). Reach for this skill before creating a service class, injecting a dependency, hooking into a kernel event, or editing config.
---

Architecture, dependency injection, and configuration patterns for Symfony 8.1 (PHP 8.2+, attributes, constructor injection).

## Use this skill when

- Creating a service/business-logic class and injecting it somewhere.
- Adding a constructor dependency (autowiring a service, parameter, or env var).
- Tagging a service, building a factory, or decorating an existing service.
- Reacting to framework events (kernel.request, kernel.exception, etc.) with a listener or subscriber.
- Dispatching your own custom event.
- Enabling/creating a bundle or editing the kernel.
- Editing `config/services.yaml`, `config/packages/*`, `config/bundles.php`, or `.env`, and using parameters, env vars, or secrets.

## Do not use this skill when

- Building controllers, routing, templates, or forms (that is application surface, not core wiring).
- Doing database/ORM, security/auth, mailing, or HTTP-client work (separate skills).
- Only running console commands with no code/config change.

## Instructions

Follow these steps in order. Do not skip ahead. Stop as soon as the task is satisfied.

1. Identify the task type and open ONLY the matching reference file from `## Reference files`. Do not read all references.
2. Confirm the default `config/services.yaml` exists with `_defaults: autowire: true, autoconfigure: true` and `App\: resource: '../src/'`. If it exists, do NOT recreate it. New classes under `src/` are auto-registered as services; you usually need NO YAML entry.
3. Inject dependencies via the constructor with promoted, `private`, type-hinted properties. Type-hint the interface when one exists. Do not write a YAML service entry just to inject another `src/` service — autowiring handles it.
4. Only add explicit config (YAML/PHP) when autowiring cannot resolve it: a scalar/parameter/env-var argument, a specific (non-default) service choice, a factory, a tag the autoconfigure does not add, or decoration.
5. Prefer PHP attributes over YAML: `#[Autowire(...)]` for arguments, `#[AsEventListener]` for listeners, `#[AsDecorator]` for decorators, `#[AutoconfigureTag]`/`#[AsTaggedItem]` for tags.
6. After editing services or config, verify with the console when available: `php bin/console debug:autowiring`, `php bin/console debug:container`, or `php bin/console debug:event-dispatcher`. Do not loop running these — run once to confirm.
7. After the change compiles/resolves, STOP. Do not refactor unrelated code or add extra abstractions.

Anti-loop rules:
- If the service/listener/config you were asked to add already exists, edit it in place; do not create a duplicate.
- Make one focused change, verify once, then stop.
- Never invent class or namespace names; use `App\` for app code and the documented Symfony FQCNs.

## Reference files

- `references/services-di.md` — open this when creating a service, injecting dependencies, using autowiring, `#[Autowire]`, parameters, factories, decoration, tags, or `_defaults`/`_instanceof`.
- `references/events.md` — open this when reacting to or dispatching events: event listeners (`#[AsEventListener]`), subscribers (`EventSubscriberInterface`), priorities, and custom events.
- `references/bundles-config.md` — open this when enabling/creating a bundle, editing the kernel, or working with `config/packages`, `config/bundles.php`, environment variables, env-var processors, or secrets.
