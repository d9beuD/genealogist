# Deployment (Symfony 8.1)

Run each command ONCE, check exit code, stop on success. Do not loop.

## Deploy steps (run in order, once each)
1. Upload/checkout code to the server.
2. Install vendors (production):
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
   - `--no-dev` skips dev packages. `--optimize-autoloader` builds a class map.
   - If you get "class not found" during this step, run `export APP_ENV=prod` first so `post-install-cmd` scripts run in the prod env.
3. Run database migrations (if any), e.g. `php bin/console doctrine:migrations:migrate --no-interaction`.
4. Clear and warm up the cache (ONCE):
   ```bash
   APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear
   ```
   `cache:clear` also warms the cache. Do NOT re-run it in a loop.

Other tasks as needed (once each): clear APCu/OPcache, restart workers, edit CRON, push assets to a CDN, compile AssetMapper assets.

## Environment variables (pick ONE option)
- Option A: real environment variables (set in the web server config, shell, or host panel).
- Option B: a `.env.prod.local` file with prod-specific values.
- Optimize: avoid parsing `.env.*` on every request by dumping an optimized file (ONCE):
  ```bash
  composer dump-env prod
  # or, to rely only on real env vars (no values baked in):
  composer dump-env prod --empty
  ```
  This generates `.env.local.php` which overrides other env config files.
  If Composer is not on the server, use `php bin/console dotenv:dump prod` instead.

## Requirements checker (optional)
```bash
composer require symfony/requirements-checker
```
Then ensure `vendor/bin/requirements-checker` is in the `auto-scripts` of `composer.json`.

## Trusted proxies / load balancer (deployment/proxies)
If the app sits behind a reverse proxy or load balancer, configure trusted proxies so Symfony reads `X-Forwarded-*` headers correctly. Set in `config/packages/framework.yaml`:
```yaml
framework:
    trusted_proxies: '127.0.0.1,REMOTE_ADDR'
    trusted_headers: ['x-forwarded-for', 'x-forwarded-host', 'x-forwarded-proto', 'x-forwarded-port', 'x-forwarded-prefix']
```
`REMOTE_ADDR` trusts the immediate upstream proxy. Without this, generated URLs and client IPs may be wrong.

## Project root note
`kernel.project_dir` is auto-detected as the directory of the main `composer.json`. If you deploy without `composer.json`, override `Kernel::getProjectDir()`.

## Done criteria
- `composer install` exits 0, migrations applied, `cache:clear` exits 0.
- Stop. Do not re-run install or cache:clear after success.

## Repo note
Deploy is API-only here; run console commands as `docker compose exec apache php bin/console ...`.
