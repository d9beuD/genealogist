# AGENTS.md

## App Shape
- `frontend/` is a standalone workspace inside the parent `genealogist` repository; do app commands here, but do repository-level work from the parent directory.
- Git, GitHub workflows, release automation, repo instructions, and other `.github`/root config changes belong in the parent directory, not this workspace.
- This is the standalone Vue/Vite frontend; run commands from `frontend/`, not the repo root.
- Entrypoints are `src/main.ts`, `src/App.vue`, and `src/router/index.ts`. The router is currently an empty shell.
- Backend API access goes through `src/api/index.ts`, using `VITE_BACKEND_BASE_URL`, `credentials: 'include'`, and JSON-LD headers.

## Commands
- Use `pnpm`, not npm/yarn. Node must satisfy `>=26.0.0`.
- Install and dev server: `pnpm install`, `pnpm dev`.
- Production checks/build: `pnpm build`; this runs `vue-tsc --build` and `vite build` in parallel via `run-p`.
- Unit tests: `pnpm test:unit`; focused non-watch run: `pnpm test:unit --run src/__tests__/App.spec.ts`.
- E2E tests: `pnpm test:e2e`; first run needs `pnpm exec playwright install`. Focus one file/browser with `pnpm test:e2e e2e/vue.spec.ts --project chromium`.
- `pnpm lint` modifies files because both oxlint and eslint use `--fix`; use it only when edits are acceptable. `pnpm format` runs `oxfmt src/`.

## Tooling Notes
- Vite aliases `@` to `src`.
- Vitest uses `jsdom` and excludes `e2e/**`.
- Playwright starts `npm run dev` locally on port `5173` and `npm run preview` in CI on port `4173`, even though project docs prefer pnpm.
- shadcn-vue is configured in `components.json` with `new-york` style, taupe base color, lucide icons, and aliases under `@/components`, `@/lib`, and `@/composables`.
