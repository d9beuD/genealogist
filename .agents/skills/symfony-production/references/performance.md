# Performance (Symfony 8.1)

Apply only the items relevant to the task. Edit config once; do not loop.

## Production server checklist
1. Dump the container into a single file (helps with preloading). In `config/services.yaml`:
   ```yaml
   parameters:
       .container.dumper.inline_factories: true
   ```
   (The `.` prefix = compile-time-only parameter.)
2. Enable OPcache (PHP ships it; enable in `php.ini` if needed).
3. Configure OPcache for Symfony in `php.ini`:
   ```ini
   opcache.memory_consumption=256
   opcache.max_accelerated_files=32531
   opcache.interned_strings_buffer=32
   ```
4. Don't check PHP file timestamps in `php.ini`:
   ```ini
   opcache.validate_timestamps=0
   ```
   IMPORTANT: after each deploy you MUST reset OPcache or updates won't show. CLI and web OPcache are separate, so a CLI command cannot clear the web OPcache. Options: restart the web server (PHP-FPM), call `opcache_reset()` via a web script, or use the `cachetool` utility.
5. Configure the realpath cache in `php.ini`:
   ```ini
   realpath_cache_size=4096K
   realpath_cache_ttl=600
   ```
   (Disabled by PHP when `open_basedir` is set.)
6. Optimize the Composer autoloader (ONCE, part of deploy):
   ```bash
   composer dump-autoload --no-dev --classmap-authoritative
   ```
   `--classmap-authoritative` prevents filesystem scans for classes not in the map.

## OPcache class preloading
Symfony generates a preload list during container compilation (`cache:clear`). Use the Flex-provided `config/preload.php`:
```ini
; php.ini
opcache.preload=/path/to/project/config/preload.php
opcache.preload_user=www-data
```
If `config/preload.php` is missing: `composer recipes:update symfony/framework-bundle`.
Control preloading with the `container.preload` / `container.no_preload` service tags.

## Other
- Restrict enabled locales: `framework.enabled_locales` to only generate used translation files.
- Disable container XML dump in debug mode (if the debug commands aren't worth the cost): set parameter `debug.container.dump: false` in `config/services.yaml`.

## Profiling code execution (dev)
- Use the Stopwatch component: type-hint `Symfony\Component\Stopwatch\Stopwatch` in a controller/service (autowired as `debug.stopwatch`), then `$sw->start('name')` / `$sw->stop('name')`. Twig: `{% stopwatch 'name' %}...{% endstopwatch %}`.
- For deep profiling use Blackfire.

## Done criteria
- `php.ini` / `services.yaml` edited as needed; `composer dump-autoload` exits 0.
- Stop. Do not re-run dump-autoload in a loop.
