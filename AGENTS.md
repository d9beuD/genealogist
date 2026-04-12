# AGENTS.md

## Stack And Shape
- Symfony 8.0 app on PHP 8.2+ via Composer; routes are attribute-based from `src/Controller/` (`config/routes.yaml`).
- Main app flow starts at `/`, which redirects to the authenticated tree area at `/project/` (`src/Controller/HomeController.php`, `src/Controller/TreeController.php`).
- Core model wiring: `User` owns many `Tree`; each `Person` belongs to one `Tree`; `Union` connects partners and children. Ownership checks are enforced with security voters, not just controller code (`src/Security/Voter/*.php`).

## Local Dev
- Run every PHP, Composer, Symfony CLI, and `bin/console` command through Docker with `docker compose exec apache ...`.
- Install dependencies with `docker compose exec apache composer install`.
- Docker flow is separate helper tooling: `make init` builds containers, starts them, then runs the in-container Symfony init step; `make up`, `make stop`, `make exec` are the main follow-ups.
- Important DB gotcha: committed `.env` uses SQLite at `var/data.db`, while `compose.yaml` starts MariaDB + phpMyAdmin but does not wire `DATABASE_URL` for you. If you use Docker DB, set `DATABASE_URL` yourself.

## Verification And Build
- PHPUnit config lives in `phpunit.xml.dist`; run focused tests with `docker compose exec apache php bin/phpunit tests/Controller/TreeControllerTest.php` after `docker compose exec apache composer install`.
- Quality tooling is installed and exposed through Composer scripts:
  - `docker compose exec apache composer analyse`
  - `docker compose exec apache composer rector`
  - `docker compose exec apache composer rector:fix`
  - `docker compose exec apache composer cs:check`
  - `docker compose exec apache composer cs:fix`
  - `docker compose exec apache composer lint`
- Be careful with the checked-in controller tests before trusting them: they are scaffold leftovers that still target `/tree/` and `/person/`, while current tree routes live under `/project`.
- Those WebTestCase tests delete repository contents in `setUp()`. Confirm your test database target before running them.
- Production build order is defined in `.github/workflows/deploy.yml`: `docker compose exec apache php bin/console sass:build`, `docker compose exec apache php bin/console asset-map:compile`, `docker compose exec apache php bin/console assets:install`, then `docker compose exec apache composer dump-env prod`, optimized autoload, and `docker compose exec apache php bin/console cache:warmup`.

## Data And Assets
- After changing Doctrine entities, create and run a migration; README explicitly calls this out, and `src/Command/PostPublishCommand.php` is built around `docker compose exec apache php bin/console doctrine:migrations:diff` + `docker compose exec apache php bin/console doctrine:migrations:migrate`.
- Frontend assets use AssetMapper + Importmap + SymfonyCasts Sass (`config/packages/asset_mapper.yaml`, `importmap.php`, `assets/app.js`); do not assume Node/Vite.
- Uploaded portraits are stored in `public/pictures` via `portraits_directory` (`config/services.yaml`, `src/Service/ImageManager.php`). Deploy intentionally excludes `public/pictures/*`, so treat it as persisted user data.

## Auth And Fixtures
- `/project`, `/person`, and `/union` are protected by `ROLE_USER` access control (`config/packages/security.yaml`). If a page unexpectedly redirects, check auth first.
- Dev/test fixtures seed one verified user: `john.doe@example.com` with password `password` (`src/DataFixtures/AppFixtures.php`).
