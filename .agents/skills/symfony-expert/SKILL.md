---
name: symfony-expert
description: Build modern Symfony 8 applications with Doctrine, Twig, Twig Components, Live Components, Symfony UX, Stimulus, Forms, Security, and Messenger.
---

# Symfony Expert

Build modern Symfony 8 applications with Doctrine, Twig, Twig Components, Live Components, Symfony UX, Stimulus, Forms, Security, and Messenger.

## Use this skill when

- Building or refactoring a Symfony 8 application.
- Designing controller, service, repository, form, or Messenger flows in Symfony.
- Creating server-rendered UI with Twig, Twig Components, or Live Components.
- Adding targeted frontend behavior with Symfony UX and Stimulus controllers.
- Debugging Doctrine queries, entity mapping, hydration, or performance issues.
- Reviewing Symfony code for framework alignment, maintainability, and delivery risk.

## Do not use this skill when

- The task is primarily a SPA architecture problem better solved inside React, Vue, or another frontend framework.
- The project is not using Symfony conventions and the user explicitly wants a framework-agnostic answer.
- The task is limited to generic PHP language mechanics without meaningful Symfony context.

## Instructions

1. Confirm the active Symfony conventions before changing architecture: directory layout, autowiring defaults, asset system, testing style, and package choices.
2. Target Symfony 8 and modern PHP 8.2+ patterns: attributes, constructor injection, readonly where appropriate, explicit types, and slim controllers.
3. Prefer Symfony-native solutions before custom abstractions: forms, validators, event subscribers, Messenger, Twig helpers, UX packages, and console tooling.
4. Keep controllers thin. Move business logic into services, handlers, domain objects, or focused application classes.
5. Treat Doctrine as a persistence layer, not a dumping ground for application orchestration. Keep repositories focused on query concerns and entity invariants inside the model.
6. Prefer Twig for rendering, Twig Components for reusable UI units, and Live Components for server-driven interactivity. Use Stimulus for targeted client behavior, browser integrations, and UX glue.
7. Preserve the project's frontend build choice. Do not force Webpack Encore or AssetMapper if the project already uses the other.
8. Keep Forms and Security as secondary but integrated concerns. Use Symfony forms, validators, voters, access control, and CSRF protection when they fit the workflow.
9. Include Messenger when work crosses process boundaries, needs retries, or benefits from async handling. Keep message payloads explicit and handlers focused.
10. Use quality tools as part of the delivery loop when available: `phpstan` for static analysis, `php-cs-fixer` for style consistency, and `rector` for safe automated modernization.
11. Validate behavior with the narrowest useful test level: unit tests for isolated logic, integration tests for repositories and handlers, and `WebTestCase` or functional tests for HTTP flows.

Refer to the bundled references for implementation details:

- `references/modern-symfony-playbook.md`
- `references/doctrine-patterns.md`
- `references/twig-and-components.md`
- `references/live-components-and-stimulus.md`

## Focus Areas

- Symfony 8 application structure, dependency injection, configuration, and HTTP flow.
- Doctrine ORM entities, repositories, migrations, fixtures, query design, and performance tuning.
- Twig templates, template composition, form rendering, and presentation boundaries.
- Twig Components for reusable UI building blocks.
- Twig Live Components for interactive server-rendered interfaces.
- Symfony UX and Stimulus controllers for progressive enhancement.
- Messenger buses, messages, handlers, transports, retries, and async workflows.
- Secondary concerns: Forms, validation, authentication, authorization, voters, and CSRF.
- Tooling: Rector, PHPStan, php-cs-fixer, PHPUnit, and Symfony console diagnostics.

## Symfony Approach

1. Start from the user-facing behavior and the existing Symfony conventions.
2. Keep HTTP, domain, persistence, rendering, and async boundaries clear.
3. Prefer explicit services and small classes over large utility layers.
4. Use framework features intentionally instead of recreating them manually.
5. Make interactive UI progressively enhanced, inspectable, and testable.
6. Optimize after identifying the real bottleneck, especially with Doctrine and Live Components.

## Output

- Symfony code that follows framework conventions and fits the existing project.
- Doctrine queries and mappings that avoid common correctness and performance traps.
- Twig and UX implementations that stay server-first and maintainable.
- Messenger flows with explicit messages, handlers, retries, and failure behavior.
- Practical verification steps, including tests and quality-tool commands when relevant.

## Resources

- `references/modern-symfony-playbook.md` for architecture, services, forms, security, Messenger, and delivery guidance.
- `references/doctrine-patterns.md` for entity design, repositories, mapping, hydration, and query optimization.
- `references/twig-and-components.md` for Twig, Twig Components, and server-rendered UI patterns.
- `references/live-components-and-stimulus.md` for Live Components, Symfony UX, and Stimulus integration patterns.
