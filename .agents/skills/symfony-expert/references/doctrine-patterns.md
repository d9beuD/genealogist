# Doctrine Patterns

Use this reference for entity design, repositories, query tuning, hydration, and persistence boundaries.

## Entities

- Map entities with PHP attributes.
- Keep entity state coherent and avoid public mutable fields.
- Put invariants and lifecycle-relevant rules close to the entity when they are truly part of the model.
- Avoid stuffing service calls, HTTP concerns, or cross-aggregate orchestration into entities.

Good entity responsibilities:

- state transitions
- invariant checks
- value object coordination

Bad entity responsibilities:

- querying other aggregates
- sending mail
- calling APIs
- reading request context

## Repositories

Repositories should focus on query intent.

- Name methods around business retrieval use cases.
- Prefer explicit query methods over generic catch-all helpers.
- Return the narrowest useful shape: entity, scalar result, DTO, or paginated slice.
- Avoid leaking query builder plumbing throughout the application.

## Query Design

Watch for common Doctrine problems:

- N+1 query patterns in loops and templates
- fetching full entities when scalar or partial data is enough
- eager joins that multiply rows unexpectedly
- hidden lazy-loading inside Twig or Live Components

Useful heuristics:

- join what the current request truly needs
- fetch related data intentionally
- move repeated query logic into repositories
- paginate large datasets instead of loading everything

## Mapping And Relations

- Keep bidirectional associations only when both directions are genuinely useful.
- Maintain both sides of a relationship consistently when using bidirectional mappings.
- Be deliberate with cascade rules; avoid broad cascade settings that hide persistence side effects.
- Prefer value objects and embeddables when a concept is not an entity.

## Migrations And Fixtures

- Keep migrations focused and readable.
- Review generated migrations before running them.
- Avoid mixing unrelated schema changes in one migration.
- Use fixtures for development and test support, not production-like business bootstrapping.

## Performance Checks

When a page or component feels slow:

- count queries first
- inspect lazy-loading boundaries
- check whether a repository is returning too much data
- look for repeated queries triggered by Twig accessors
- verify indexes at the database level when filtering or sorting heavily

## Messenger And Doctrine

When dispatching Messenger work from Doctrine-backed flows:

- pass stable identifiers instead of entity objects where possible
- reload fresh state in the handler when consistency matters
- design handlers to tolerate retries and stale assumptions
- be explicit about transaction boundaries

This keeps async behavior robust and easier to reason about.
