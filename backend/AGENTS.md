# AGENTS.md

## Stack And Shape
- Symfony 8.0 app on PHP 8.4+ via Composer, being migrated from a fullstack Symfony app to a stateless Symfony + API Platform backend.
- Prefer API Platform resources, state providers/processors, DTOs, serializers, validation constraints, and security expressions over new HTML controllers or Twig-driven flows.
- New write-side API endpoints should follow the current registration shape: API Platform resource in `src/ApiResource`, input/output DTOs in `src/Dto`, thin state processor in `src/State`, and application use case/command classes under `src/Application/<Domain>` for business rules.
- Existing MVC routes/controllers may still exist during the migration; treat them as legacy unless the task explicitly asks to maintain server-rendered behavior.
- Core model wiring: `User` owns many `Tree`; each `Person` belongs to one `Tree`; `Union` connects partners and children. Ownership checks should remain enforced at the API/security layer, including voters where they are already the domain authorization boundary (`src/Security/Voter/*.php`).
- The frontend is now a separate Vue/Vite app in `../frontend`; do not add AssetMapper/Importmap/Sass UI work to the backend unless preserving legacy behavior is explicitly requested.
- API Platform accepts both JSON-LD and plain JSON (`application/json`) in `config/packages/api_platform.yaml`; keep plain JSON working for frontend-facing endpoints.

## Local Dev
- Run every PHP, Composer, Symfony CLI, and `bin/console` command through Docker with `docker compose exec apache ...`.
- Install dependencies with `docker compose exec apache composer install`.
- Docker flow is separate helper tooling: `make init` builds containers, starts them, then runs the in-container Symfony init step; `make up`, `make stop`, `make exec` are the main follow-ups.
- Important DB gotcha: committed `.env` uses SQLite at `var/data.db`, while `compose.yaml` starts MariaDB + phpMyAdmin but does not wire `DATABASE_URL` for you. If you use Docker DB, set `DATABASE_URL` yourself.

## Verification And Build
- PHPUnit config lives in `phpunit.xml.dist`; after `docker compose exec apache composer install`, run focused tests with `docker compose exec apache php bin/phpunit <path>`.
- Quality tooling is installed and exposed through Composer scripts:
  - `docker compose exec apache composer analyse`
  - `docker compose exec apache composer rector`
  - `docker compose exec apache composer rector:fix`
  - `docker compose exec apache composer cs:check`
  - `docker compose exec apache composer cs:fix`
  - `docker compose exec apache composer lint`
- Be careful with checked-in controller/WebTestCase tests before trusting them: some may still reflect legacy fullstack routes such as `/tree/`, `/person/`, or `/project` instead of API Platform endpoints.
- Those WebTestCase tests delete repository contents in `setUp()`. Confirm your test database target before running them.
- Backend production deploy should be treated as API-only unless the workflow still contains legacy asset steps. Keep root `.github/workflows/deploy.yml` in sync as AssetMapper/Twig dependencies are removed.
- Registration/auth API coverage lives in `tests/Api/RegistrationTest.php` and `tests/Api/AuthenticationTest.php`; update these focused tests when touching `/api/register`, `/api/auth`, refresh-token cookies, CSRF, or translated validation errors.

## Data And Assets
- After changing Doctrine entities, create and run a migration; README explicitly calls this out, and `src/Command/PostPublishCommand.php` is built around `docker compose exec apache php bin/console doctrine:migrations:diff` + `docker compose exec apache php bin/console doctrine:migrations:migrate`.
- Frontend assets live in `../frontend` and use Vue/Vite/pnpm. Backend asset files/config may exist only as migration leftovers unless a task says otherwise.
- Uploaded portraits are stored in `public/pictures` via `portraits_directory` (`config/services.yaml`, `src/Service/ImageManager.php`). Deploy intentionally excludes `public/pictures/*`, so treat it as persisted user data. Preserve or replace this API behavior deliberately during the migration.

## Auth And Fixtures
- The target backend is stateless: prefer token/API authentication, explicit API access control, and JSON error responses over redirects, sessions, and form-login assumptions.
- JWT login is exposed at `POST /api/auth`; token refresh is exposed at `POST /api/token/refresh`.
- Public API registration is exposed at `POST /api/register` through API Platform. It creates an unverified `User` (`isVerified = false`) but login must remain allowed immediately unless a task explicitly changes that behavior.
- `POST /api/register` is intentionally `PUBLIC_ACCESS` and exempt from the API CSRF cookie/header check. Keep any future CSRF exemptions similarly narrow in `src/EventSubscriber/ApiSecuritySubscriber.php`.
- API validation messages should use translation keys in the `validators` domain with entries in both `translations/validators.en.yaml` and `translations/validators.fr.yaml`. API locale follows `Accept-Language` via framework locale configuration.
- Legacy `/project`, `/person`, and `/union` routes may still be protected by `ROLE_USER` access control (`config/packages/security.yaml`) while migration is in progress. If an API request unexpectedly redirects, check for leftover session/form-login configuration.
- Dev/test fixtures seed one verified user: `john.doe@example.com` with password `password` (`src/DataFixtures/AppFixtures.php`).
