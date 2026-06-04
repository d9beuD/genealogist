# Twig And Components

Use this reference for server-rendered UI, Twig composition, and reusable component design.

## Twig First

Prefer Twig when the UI is primarily server-rendered and benefits from Symfony's form, translation, security, and routing integration.

Keep templates:

- presentational
- easy to scan
- free of heavy business logic

Move complex branching or data assembly back into services, view models, or component classes.

## Template Composition

- Use inheritance and includes intentionally.
- Prefer a few clear blocks over deeply nested template trees.
- Avoid repeating markup that should be a component.
- Avoid hidden database access triggered by template property access.

## Twig Components

Use Twig Components for reusable UI units with clear inputs and predictable markup.

Good fits:

- alerts, cards, tables, filters, dialogs, form wrappers, dashboard widgets
- components that need injected services or computed state
- UI that should stay server-first but reusable across pages

Prefer:

- clear public props
- defaults that keep rendering simple
- `mount()` or pre-mount validation when input shaping matters
- one clear root element when integration with Live Components or Stimulus is likely

Be careful with:

- overloading a component with too many modes
- mixing query-heavy work into render paths without repository discipline
- hard-coding CSS or JS assumptions that the host page cannot override

## Component Templates

- Use `attributes.defaults()` for flexible root attributes.
- Let consumers pass classes and HTML attributes cleanly.
- Favor block-based extension points when markup needs controlled customization.
- Keep component naming and directory layout consistent.

## Forms In Twig

- Let Symfony form rendering handle the common path.
- Customize rows or wrappers without duplicating the whole form theme unless necessary.
- Use components to wrap recurring form layouts when they are repeated across the app.

## Security In Templates

- Use template checks for presentation concerns, not as the only enforcement layer.
- Keep real authorization in controllers, voters, or application services.

## Debugging

Helpful tools and checks:

- `php bin/console debug:twig`
- `php bin/console debug:twig-component`
- inspect component props and rendered attributes carefully
- watch for hidden lazy-loading triggered by template access
