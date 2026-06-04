# Vue.js 3.5 Patterns

## Quick Decision Guide

Use `ref` when the value is a primitive, may be replaced wholesale, or must remain easy to pass across composables.

Use `reactive` when the value is a stable object model with multiple fields that are usually updated together.

Use `computed` when a value can be derived from existing reactive state without side effects.

Use `watch` when code must react to a specific source and perform a side effect such as fetching, persistence, analytics, or imperative integration.

Use `watchEffect` when dependencies are naturally discovered and the effect is short-lived, local, and easy to reason about.

Use `shallowRef` for large immutable payloads, external library instances, editor objects, chart instances, and values that should only trigger updates when replaced.

## Vue 3.5 Features To Prefer

- `useTemplateRef` for typed template refs.
- `onWatcherCleanup` for aborting stale async work inside watchers.
- Reactive props destructure when the project compiler supports it and the team already uses it.
- `defineModel` for intentional two-way component bindings.

## Composable Checklist

- Name composables with a `use` prefix.
- Accept refs or plain values intentionally; normalize with `toValue` when supporting both.
- Return refs rather than plain snapshots when consumers need reactivity.
- Keep lifecycle hooks inside composables only when the composable is component-scoped.
- Expose `stop`, `reset`, or `refresh` functions when consumers need control.
- Avoid hidden global state unless the composable is documented as a singleton.

## Component Review Checklist

- Props are typed and not mutated.
- Emits are typed and named after user intent, not implementation details.
- Derived values use `computed`, not duplicated state.
- Watchers have explicit sources and cleanup for async work.
- Lists use stable keys.
- Conditional rendering does not break focus, labels, or accessible names.
- Browser-only APIs are not used during SSR setup.
- Expensive children are lazy-loaded only when it improves the actual route or interaction path.

## Common Anti-Patterns

### Duplicating Derived State

Avoid storing values that can be derived from existing refs.

```ts
// Avoid
const firstName = ref('Ada');
const lastName = ref('Lovelace');
const fullName = ref('Ada Lovelace');

watch([firstName, lastName], () => {
  fullName.value = `${firstName.value} ${lastName.value}`;
});

// Prefer
const fullName = computed(() => `${firstName.value} ${lastName.value}`);
```

### Deep Watching Large Objects

Deep watchers are easy to write and hard to maintain. Prefer explicit sources.

```ts
watch(
  () => [filters.status, filters.search, filters.page],
  () => refreshResults(),
);
```

### Prop Mirroring Without Intent

Avoid copying props into local state unless creating a draft that can diverge from the parent.

```ts
const props = defineProps<{ title: string }>();

// Prefer direct usage or computed derivation.
const heading = computed(() => props.title.trim());
```

## SSR Safety

- Do not read `window`, `document`, `localStorage`, layout measurements, or media queries at module scope or during universal setup.
- Use `onMounted` for browser-only initialization.
- Keep server-rendered markup deterministic between server and client.
- Avoid rendering time-, random-, or locale-dependent values unless they are serialized consistently.

## Performance Triage

1. Reproduce the slow interaction or heavy route.
2. Check bundle composition and route-level code splitting.
3. Inspect large reactive payloads and unnecessary deep reactivity.
4. Check list keys and repeated expensive child renders.
5. Apply targeted fixes such as async components, `shallowRef`, virtualization, or `v-memo`.
6. Re-run the same measurement after each change.
