# AGENTS.md

## Repo Shape
- This is a split repo, not a single workspace: `backend/` is the Symfony app and `frontend/` is a separate Vue/Vite app. Run commands from the matching subdirectory.
- Backend-specific guidance lives in `backend/AGENTS.md`; read it before touching Symfony code.
- `frontend/` is a fresh Vue app shell: entrypoints are `src/main.ts`, `src/App.vue`, and `src/router/index.ts`; API calls use `src/api/index.ts` with `VITE_BACKEND_BASE_URL` and `credentials: 'include'`.

## Frontend Commands
- Use `pnpm`, not npm/yarn. Node must satisfy `>=26.0.0`.
- From `frontend/`: `pnpm install`, `pnpm dev`, `pnpm build`, `pnpm preview`.
- `pnpm build` runs `vue-tsc --build` and `vite build` in parallel via `run-p`.
- Unit tests: `pnpm test:unit`; focused non-watch run: `pnpm test:unit --run src/__tests__/App.spec.ts`.
- E2E tests: `pnpm test:e2e`; first run needs `pnpm exec playwright install`. Focus one file/browser with `pnpm test:e2e e2e/vue.spec.ts --project chromium`.
- `pnpm lint` fixes files because both oxlint and eslint scripts use `--fix`; use it only when edits are acceptable. `pnpm format` runs `oxfmt src/`.

## Frontend Tooling Notes
- Vite aliases `@` to `frontend/src`.
- Vitest uses `jsdom` and excludes `e2e/**`.
- Playwright starts `npm run dev` locally on port `5173` and `npm run preview` in CI on port `4173`, even though the repo docs prefer pnpm.
- shadcn-vue is configured by `frontend/components.json` with `new-york` style, taupe base color, lucide icons, and aliases under `@/components`, `@/lib`, and `@/composables`.

## Backend And Release Notes
- Backend requires PHP `>=8.4` and Symfony `8.0.*`; CI deploy also sets up PHP 8.4.
- Root release automation uses `release-please` on `main` with conventional commit sections from `release-please-config.json`.
- Deploy workflow runs on published releases or manual dispatch and excludes `public/pictures/*`; treat uploaded portraits as persisted user data.
