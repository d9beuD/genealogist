---
name: symfony-production
description: Use for Symfony 8.1 deployment, production configuration, and operations. Covers deploying (APP_ENV=prod, composer install --no-dev --optimize-autoloader, cache:clear/warmup, dump-env), performance tuning (OPcache, realpath cache, preloading, container single-file, classmap-authoritative), HTTP caching (Cache-Control, Expires, ETag, Last-Modified, Vary, reverse proxy, invalidation/PURGE), profiler and debugging, logging with Monolog (handlers, channels, fingers_crossed), testing with PHPUnit (WebTestCase, KernelTestCase, assertions, database), and Symfony best practices. Trigger whenever a task mentions deploy, prod env, opcache, performance, HTTP cache, reverse proxy, profiler, Monolog/logs, PHPUnit tests, or production hardening of a Symfony app.
---

Deterministic playbook for shipping and operating Symfony 8.1 apps in production.

## Use this skill when
- Deploying a Symfony 8.1 app or building a deploy pipeline (prod env, vendors, cache).
- Tuning production performance (OPcache, realpath cache, preloading, Composer autoloader).
- Adding or debugging HTTP caching (Cache-Control, Expires, ETag, Last-Modified, Vary, PURGE).
- Configuring the profiler or accessing profiling data.
- Configuring logging / Monolog (handlers, channels, fingers_crossed).
- Writing or fixing PHPUnit tests (WebTestCase, KernelTestCase, assertions, DB tests).
- Applying Symfony best practices (config, services, controllers, security, tests).

## Do not use this skill when
- Writing business logic, routes, controllers, forms, or Doctrine mappings (different skill).
- Configuring authentication/authorization internals (security skill).
- Building frontend assets beyond a deploy note (frontend skill).

## Instructions
Follow these steps in order. Do ONE task per request. Stop as soon as the done criterion is met.

1. **Identify the task** and map it to exactly ONE reference file (see Reference files). Open only that file.
2. **Run the matching command(s) exactly as written** in the reference. Do not invent flags.
3. **Run each command ONCE. Check the exit code.**
   - Exit code 0 = success. STOP. Do not re-run.
   - Non-zero = read the error, fix the one cause named in the error, run again ONCE. After 2 failures, report the error and stop. Never loop.
4. **Never re-run `cache:clear`, `cache:warmup`, `composer install`, or `dump-env` in a loop.** Each runs once per deploy. If output already shows success, move on.
5. **For config edits**: make the change in the file the reference names, then run the single verification command the reference gives (if any). Do not re-edit repeatedly.
6. **For code/tests**: write the file, then run `php bin/phpunit` (or the named script) ONCE. Read failures, fix, re-run ONCE. Stop after green or after 2 attempts.
7. **Production safety (always enforce)**: profiler/web debug toolbar must NEVER be enabled in prod. `APP_ENV=prod` and `APP_DEBUG=0` for all prod commands.
8. **Report** what you changed/ran and the result. Then STOP.

### Done / stop criteria
- Command exited 0, OR config file saved and verification passed, OR tests green.
- Do NOT keep clearing caches, re-running installs, or re-reading references after success.

### Repo conventions (this project; still generally applicable)
- Deploy is API-only.
- Commands run inside the container: prefix with `docker compose exec apache ` (e.g. `docker compose exec apache php bin/console cache:clear`).
- Quality tools are composer scripts: `composer analyse`, `composer rector`, `composer cs:check`, `composer cs:fix`, `composer lint`.
- Tests run with `php bin/phpunit`.

## Reference files
- `references/deployment.md` — open when deploying or building a deploy pipeline: APP_ENV=prod, composer install --no-dev, dump-env, cache:clear/warmup, migrations, env vars, trusted proxies.
- `references/performance.md` — open when tuning prod performance: OPcache settings, validate_timestamps, realpath cache, preloading, container single-file, classmap-authoritative.
- `references/http-cache.md` — open for HTTP caching: reverse proxy, Cache-Control/Expires (expiration), ETag/Last-Modified (validation), Vary, invalidation/PURGE.
- `references/logging-profiler.md` — open for Monolog (handlers, channels, fingers_crossed, where logs go) and the profiler (enable/disable, access data, prod safety).
- `references/testing.md` — open for PHPUnit: WebTestCase, KernelTestCase, getContainer, assertions, loginUser, DB tests, profiling in tests.
- `references/best-practices.md` — open for Symfony conventions: config (env vars/secrets/parameters/constants), services, controllers, templates, forms, i18n, tests.
