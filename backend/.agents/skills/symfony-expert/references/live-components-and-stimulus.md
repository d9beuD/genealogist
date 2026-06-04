# Live Components And Stimulus

Use this reference for interactive Symfony UX work that stays server-driven and progressively enhanced.

## When To Use Live Components

Use Live Components when:

- the UI should stay server-rendered
- the interaction model is incremental, not SPA-sized
- forms, filtering, sorting, previews, or inline updates benefit from round-trip rendering
- Symfony form, validation, security, and Twig integration are valuable

Prefer standard Twig Components when interactivity is not needed.

## Live Component Defaults

- Start from a good Twig Component design.
- Add `AsLiveComponent` only when stateful interactivity is useful.
- Keep one clear root element.
- Use `LiveProp` deliberately and make writable props explicit.
- Keep data flow easy to reason about.

Common good fits:

- search and filtering panels
- inline editors
- cart or checkout side panels
- dashboard widgets with controls
- form-driven previews and dependent field updates

## Live Component Risks

Watch for:

- hidden query explosions on each re-render
- writable props that expose more state than intended
- large component classes doing querying, validation, and orchestration all at once
- unstable DOM structures that make rerender behavior harder to reason about

When a live component becomes too large, split responsibilities or move parts back to regular Twig Components plus Stimulus.

## Stimulus Controllers

Use Stimulus for browser behavior that should stay local and explicit.

Good fits:

- modal state
- clipboard actions
- keyboard shortcuts
- intersection observers
- third-party JS integration
- enhancing a Live Component root with targeted browser hooks

Prefer:

- small controllers
- clear targets, values, and actions
- Twig helpers or explicit data attributes that match project conventions
- lazy loading when a controller is page-specific and the project uses it

## Symfony UX Integration

- Keep UX packages aligned with the existing asset system.
- Do not force AssetMapper or Encore migration as part of feature work.
- Use Twig helpers when they improve consistency, but raw data attributes are also acceptable when clearer.
- When attaching Stimulus to component roots, ensure the root attributes merge cleanly.

## Live Components With Doctrine

Live Components and Doctrine can interact well, but query discipline matters.

- avoid loading large collections repeatedly on each keystroke
- debounce or defer when the UX allows it
- prefer repository methods shaped to the component use case
- keep entity hydration and serialization costs visible
- do not let template access trigger surprise lazy loads

## Testing And Verification

- test component behavior at the smallest useful level
- cover live actions and writable props with focused tests
- verify loading and rerender behavior for critical interactions
- exercise Stimulus integration where a browser-side hook is central to the feature

## Quality Tools

Use `phpstan`, `php-cs-fixer`, and `rector` alongside UX work too. Interactive features often fail at the boundaries between PHP, Twig, and JavaScript, and these tools help keep the PHP side predictable while you review the rendered behavior manually.
