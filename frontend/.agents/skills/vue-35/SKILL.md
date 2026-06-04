---
name: vue-35
description: Build and refactor Vue.js 3.5 applications with Composition API, script setup, TypeScript, Vapor-ready patterns, SSR, and performance-focused reactivity.
metadata:
  category: JS:TS
  source: self
  date_added: '2026-06-04'
---

# Vue.js 3.5 Expert

Use this skill for production Vue 3.5 work with the Composition API, `<script setup>`, TypeScript, modern reactivity primitives, server rendering, and maintainable component architecture.

## When to Use

- Building Vue 3.5 components, composables, pages, or plugins
- Refactoring Options API code toward Composition API patterns
- Designing reactive state with `ref`, `reactive`, `computed`, `watch`, Pinia, or VueUse
- Improving performance, hydration, bundle size, or rendering behavior
- Implementing Vue Router, SSR, Nuxt-compatible patterns, forms, or async UI states
- Reviewing Vue code for correctness, accessibility, and maintainability

## Do Not Use

- General JavaScript or TypeScript problems with no Vue-specific behavior; use `javascript-pro` or `typescript-pro`
- Angular, React, Svelte, or backend framework work
- Legacy Vue 2 migration plans that require compatibility-build strategy beyond Vue 3.5 implementation details
- Visual design-only requests where framework choices are irrelevant

## Instructions

1. Inspect the project structure, Vue version, build tool, router, state library, and test setup before changing code.
2. Prefer Vue 3.5 idioms: Composition API, `<script setup>`, typed props and emits, composables for reusable logic, and explicit async states.
3. Keep reactivity simple. Use `ref` for primitives and replaceable values, `reactive` for stable object models, `computed` for derivation, and `watch` only for side effects.
4. Preserve the existing design system, folder conventions, linting style, and component boundaries.
5. Validate with the narrowest relevant command: typecheck, unit tests, component tests, build, or existing framework checks.

## Core Defaults

- Write Single File Components with `<script setup lang="ts">` unless the project uses another established convention.
- Use `defineProps`, `defineEmits`, `defineModel`, and `withDefaults` instead of runtime prop boilerplate when TypeScript support is available.
- Use `computed` instead of watchers for derived values.
- Use `watchEffect` sparingly; prefer explicit `watch` sources for side effects that need control.
- Use `shallowRef` or `markRaw` for large immutable objects, third-party instances, maps, charts, editors, and non-reactive class objects.
- Use `toRef` or `toRefs` when destructuring reactive objects that must remain reactive.
- Use `onWatcherCleanup` for cancelling async effects started inside watchers.
- Use `useTemplateRef` for typed template refs in Vue 3.5.

## Component Pattern


```vue
<script setup lang="ts">
import { computed } from 'vue';

type UserCardProps = {
  id: string;
  name: string;
  role?: string;
  selected?: boolean;
};

const props = withDefaults(defineProps<UserCardProps>(), {
  role: 'Member',
  selected: false,
});

const emit = defineEmits<{
  select: [id: string];
}>();

const label = computed(() => `${props.name} (${props.role})`);
</script>

<template>
  <article :class="['user-card', { 'is-selected': selected }]">
    <h3>{{ label }}</h3>
    <button type="button" @click="emit('select', id)">Select</button>
  </article>
</template>
```

## Reactivity Rules

- Keep state ownership clear. Component-local UI state belongs in the component; cross-route or shared domain state belongs in Pinia or a documented composable.
- Avoid copying props into local state unless the component intentionally creates an editable draft.
- Never mutate props directly. Emit events or use `defineModel` for intentional two-way binding.
- Avoid deep watchers by default. Prefer normalized state, explicit fields, or computed projections.
- Avoid destructuring `props` unless using compiler-supported reactive props destructure or `toRefs` in projects configured for it.

## Async And Data Fetching

- Model async UI with explicit `idle`, `loading`, `success`, and `error` states when the result drives visible UI.
- Cancel stale requests in watchers with `onWatcherCleanup` and `AbortController`.
- Avoid sequential waterfalls when data can load in parallel.
- Keep fetch logic in route loaders, framework data functions, composables, or stores according to the project convention.
- For SSR, avoid browser-only globals during setup. Gate them behind lifecycle hooks or environment checks.

```ts
import { ref, watch, onWatcherCleanup } from 'vue';

const userId = ref('');
const user = ref<User | null>(null);
const error = ref<Error | null>(null);

watch(userId, async (id) => {
  if (!id) return;

  const controller = new AbortController();
  onWatcherCleanup(() => controller.abort());

  try {
    error.value = null;
    const response = await fetch(`/api/users/${id}`, { signal: controller.signal });
    user.value = await response.json();
  } catch (err) {
    if (!controller.signal.aborted) error.value = err as Error;
  }
});
```

## State Management

- Use Pinia for shared client state, authenticated user state, cached domain entities, and actions reused across routes.
- Keep Pinia stores focused around one domain concept. Avoid dumping unrelated UI flags into a global store.
- Store serializable state when SSR, persistence, devtools, or testing depends on it.
- Prefer setup stores for Composition API consistency.
- Keep server state separate from client UI state when a query library or framework data layer already owns fetching and caching.

## Routing

- Use route-level code splitting for heavy pages.
- Keep navigation guards small and deterministic.
- Validate route params and query values before using them as typed domain values.
- Use route meta for layout, authorization, and page-level concerns only when the project already follows that pattern.

## Performance

- Add stable `:key` values for `v-for` and avoid index keys for mutable lists.
- Use `v-show` for frequently toggled elements and `v-if` for conditional branches that should not mount until needed.
- Use `v-memo` only for proven hot render paths with stable dependencies.
- Use `defineAsyncComponent` and route-level dynamic imports for heavy or rarely used components.
- Avoid making large immutable payloads deeply reactive; use `shallowRef`, `shallowReactive`, or raw objects when appropriate.
- Measure before complex optimization. Prefer simple render and bundle fixes first.

## Forms And Accessibility

- Use semantic HTML controls before custom widgets.
- Connect labels, descriptions, and errors with `for`, `aria-describedby`, and clear validation messages.
- Keep keyboard navigation and focus behavior intact when conditionally rendering form sections or dialogs.
- Prefer controlled validation flow with visible pending, success, and error states.

## Testing

- Use Vue Test Utils or the existing component testing stack for component behavior.
- Test emitted events, rendered output, accessibility-relevant states, and async transitions.
- Mock network boundaries at the API/composable layer, not inside unrelated component internals.
- Use typecheck and build commands to catch template type errors.

## Output Expectations

- Vue components that match existing project conventions
- Typed props, emits, models, composables, and stores where useful
- Clear handling for loading, empty, error, and success states
- Minimal changes that preserve current behavior unless a behavior change is requested
- Verification notes with commands run and any limitations

## Resources

- `references/vue-35-patterns.md` for deeper examples and review checklists
