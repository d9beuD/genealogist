# Modern Symfony Playbook

Use this reference when a task needs deeper Symfony implementation guidance than the main skill should hold.

## Core Defaults

- Target Symfony 8 and PHP 8.2+.
- Prefer attributes, constructor injection, explicit return types, and small classes.
- Keep controllers thin and orchestration-focused.
- Prefer framework features before custom infrastructure.
- Match existing conventions before introducing a new pattern.

## Application Structure

Good Symfony work usually separates concerns clearly:

- controllers translate HTTP input into application calls and responses
- services coordinate business use cases
- repositories handle querying and persistence concerns
- entities hold state and invariants that belong to the model
- templates and components handle presentation
- Messenger handlers isolate async work and cross-process behavior

Avoid moving everything into controllers, entities, or generic helper classes.

## Services And Dependency Injection

- Use constructor injection by default.
- Prefer autowiring and autoconfiguration unless the project already uses explicit service definitions heavily.
- Keep service classes focused around one responsibility.
- Use interfaces where they create a meaningful seam, not by reflex.
- Reach for service subscribers or locators only when the dependency shape truly requires it.

## Controllers

Controllers should:

- validate route and request context
- delegate work to services, handlers, or repositories
- build a response or redirect
- avoid owning complex branching business rules

Good signs:

- short methods
- clear request to application flow
- minimal persistence logic inline

Bad signs:

- large inline entity mutation blocks
- many nested conditionals
- manual container access
- repeated query logic across controllers

## Forms And Validation

Treat Forms and Security as secondary concerns, but wire them in cleanly.

- Use Symfony Forms when request mapping, validation, and rendering all matter.
- Keep custom form types small and intention-revealing.
- Put field-level and object-level rules in validators or constraints instead of ad hoc controller branches.
- Prefer DTO-backed forms when editing input shape differs from persistence shape.
- Use form events sparingly. Reach for them when they truly simplify dynamic fields or normalization.

## Security

- Prefer built-in authenticators, access control, voters, and CSRF protection.
- Use voters for entity or business decisions that go beyond simple role checks.
- Keep authorization close to the boundary where a decision matters.
- Do not bury authorization in templates alone.
- Avoid mixing persistence side effects into voters.

## Messenger

Include Messenger whenever work benefits from queueing, retries, isolation, or asynchronous execution.

Use Messenger for:

- email or notification processing
- file generation or media processing
- external API syncs
- workflows that should not block the HTTP response
- command-style background work with explicit failure handling

Prefer:

- explicit message classes with narrow payloads
- one clear handler per message in the common case
- idempotent handlers when retries are possible
- clear transport and retry behavior
- failure transport visibility for operational recovery

Be careful with:

- passing fully hydrated entities instead of stable identifiers
- hiding critical sync logic behind async dispatch without user-facing feedback
- handlers that do too much orchestration, IO, and domain mutation at once

## Testing Levels

Choose the narrowest test that proves the behavior:

- unit tests for pure business logic and value transformations
- integration tests for repositories, Messenger handlers, and container-wired services
- `KernelTestCase` when booting the container is sufficient
- `WebTestCase` or functional tests for HTTP and security flows

When debugging a Symfony issue, also use framework diagnostics:

- `php bin/console debug:container`
- `php bin/console debug:router`
- `php bin/console debug:event-dispatcher`
- `php bin/console debug:twig-component`

## Quality Tools

Use these tools when available in the project:

- `phpstan` to catch type and API mistakes early
- `php-cs-fixer` to enforce formatting and style consistency
- `rector` to automate safe refactors and framework modernization

Recommended usage patterns:

- run `phpstan` after structural changes
- run `php-cs-fixer` after edits that touch multiple PHP files
- use `rector` intentionally, review the diff, and avoid broad automated rewrites without need

Rector is especially useful for Symfony and PHP upgrades, but it should support human judgment, not replace it.
