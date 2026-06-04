# Vue.js 3.5 Skill

This skill provides practical guidance for building, reviewing, and refactoring Vue.js 3.5 applications. It focuses on production patterns rather than broad framework introductions.

Use it for tasks involving Vue Single File Components, Composition API, `<script setup>`, TypeScript, Pinia, Vue Router, SSR-safe code, async UI states, and rendering performance.

## Typical Requests

- "Create a Vue 3.5 component for this settings form."
- "Refactor this Options API component to Composition API."
- "Review this Pinia store and route guard."
- "Fix this hydration mismatch in a Vue app."
- "Improve the performance of this large Vue list."

## What It Prioritizes

- Small, idiomatic Vue changes
- Correct use of `ref`, `reactive`, `computed`, `watch`, and `onWatcherCleanup`
- Typed props, emits, models, and template refs
- Clear loading, empty, error, and success states
- SSR-safe access to browser APIs
- Maintainable component and composable boundaries
- Verification with the project's existing build, typecheck, or test commands

## Files

- `SKILL.md` contains the operational instructions.
- `references/vue-35-patterns.md` contains deeper examples, checklists, and anti-patterns.

## Notes

The skill assumes Vue 3.5 or newer. For general JavaScript and TypeScript work, use the JavaScript or TypeScript skills instead. For visual design-only work, use a UX or UI design skill.
