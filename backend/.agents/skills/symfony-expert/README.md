# symfony-expert

**Build modern Symfony 8 applications with server-rendered UI, Doctrine, Messenger, and Symfony UX.**

## What It Does

The `symfony-expert` skill helps with real-world Symfony 8 development using modern PHP 8.2+ and the current Symfony UI stack. It is designed for application work that stays close to Symfony conventions: thin controllers, explicit services, focused repositories, Twig-first rendering, progressive enhancement, and quality checks that support maintainable delivery.

The skill is strongest when a project uses Doctrine ORM, Twig, Twig Components, Twig Live Components, Symfony UX, Stimulus controllers, Forms, Security, and Messenger. It treats Forms and Security as important supporting layers, and includes Messenger for async workflows, retries, and cross-process boundaries.

## When to Use

Use this skill when you want help with:

- building or refactoring a Symfony 8 feature
- creating Doctrine entities, repositories, migrations, or optimized queries
- building reusable UI with Twig Components
- adding interactivity with Live Components and Stimulus
- wiring Messenger messages and handlers
- reviewing Symfony code for architecture, correctness, or maintainability
- improving delivery quality with Rector, PHPStan, and php-cs-fixer

## Example Requests

- `Build a Symfony 8 CRUD screen with Doctrine, a form, and Twig Components.`
- `Convert this Twig partial into a reusable Twig Component.`
- `Turn this component into a Live Component with server-side filtering.`
- `Add a Stimulus controller for a modal and wire it through Twig helpers.`
- `Refactor this Doctrine repository method to avoid N+1 queries.`
- `Implement a Messenger message and handler for async invoice generation.`
- `Review this Symfony controller and service flow for framework alignment.`
- `Modernize this Symfony codebase with Rector, PHPStan, and php-cs-fixer guidance.`

## How It Works

The skill keeps Symfony work structured around a few strong defaults:

- controllers stay thin
- services and handlers carry application logic
- Doctrine repositories focus on querying
- Twig owns presentation
- Twig Components and Live Components structure reusable UI
- Stimulus adds targeted browser behavior
- Messenger handles async workflows where retries and isolation matter

It also recognizes project constraints instead of forcing a new stack. If a project already uses AssetMapper or Encore, existing structure and tooling should be preserved.

## Bundled References

The skill includes deeper guidance in these files:

- `references/modern-symfony-playbook.md`
- `references/doctrine-patterns.md`
- `references/twig-and-components.md`
- `references/live-components-and-stimulus.md`

These references cover framework conventions, Doctrine patterns, component design, Live Component integration, Stimulus usage, Messenger design, and quality tooling.

## Quality Tools

The skill explicitly supports:

- `phpstan` for static analysis
- `php-cs-fixer` for style consistency
- `rector` for automated modernization and framework upgrades

Use these as part of the delivery loop when the project already has them or when the user asks for modernization guidance.
