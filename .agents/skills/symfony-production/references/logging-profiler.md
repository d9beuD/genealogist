# Logging & Profiler (Symfony 8.1)

## Profiler — production safety FIRST
NEVER enable the profiler or web debug toolbar in production: it causes major security vulnerabilities. It is a dev-only tool.

Install (dev only): `composer require --dev symfony/profiler-pack`.

Access profiling data programmatically (the `profiler` service is autowired via the `Profiler` type-hint):
```php
$profile = $profiler->loadProfileFromResponse($response);
$token   = $response->headers->get('X-Debug-Token');
$profile = $profiler->loadProfile($token);
$tokens  = $profiler->find('', '/admin/', 10, '', '', ''); // ip, url, limit, method, start, end
```
For non-HTML responses the profiler URL is in the `X-Debug-Token-Link` header. List collectors: `php bin/console debug:container --tag=data_collector`.

Disable for one action: type-hint `?Profiler $profiler` and call `$profiler?->disable();` (alias `Profiler` to `@profiler` under `when@dev`). Conditional collection: `framework.profiler.collect_parameter`.

## Logging / Monolog
Install Monolog: `composer require symfony/monolog-bundle`.

Log from a service/controller (inject `Psr\Log\LoggerInterface`):
```php
$logger->info('User {userId} logged in', ['userId' => $id]); // use placeholders, not concatenation
$logger->error('An error occurred');
$logger->critical('Oven left on', ['cause' => 'in_hurry']);
```

### Where logs go
- `dev`: `var/log/dev.log`.
- `prod`: STDERR by default (good for containers). To use a file, set the handler `path` (e.g. `var/log/prod.log`); Monolog creates the directory.

### Handlers (stack; configure in `config/packages/prod/monolog.yaml`)
```yaml
monolog:
    handlers:
        file_log:
            type: stream
            path: "%kernel.logs_dir%/%kernel.environment%.log"
            level: debug
        syslog_handler:
            type: syslog
            level: error
            priority: 10   # higher priority = called first
```

### fingers_crossed (prod default pattern)
Buffers all logs and only flushes them to a nested handler when one message reaches `action_level`. Great for capturing full context only on errors:
```yaml
monolog:
    handlers:
        filter_for_errors:
            type: fingers_crossed
            action_level: error
            handler: file_log      # nested handler
        file_log:                  # not in the stack itself; used by fingers_crossed
            type: stream
            path: "%kernel.logs_dir%/%kernel.environment%.log"
```

### Channels (categories: app, doctrine, event, security, request, ...)
Route a channel to its own file. `channels` works on TOP-LEVEL handlers only (ignored when nested in group/buffer/fingers_crossed):
```yaml
when@prod:
    monolog:
        handlers:
            security:
                type: stream
                path: "%kernel.logs_dir%/security.log"
                level: debug
                channels: [security]
            main:
                # channels: ['!security']   # exclude security from main
```
Syntax: omit `channels` = all; `channels: foo` = only foo; `channels: '!foo'` = all except foo.
Each channel is the service `monolog.logger.XXX`. List: `php bin/console debug:container monolog`.

## Done criteria
- Monolog config edited; verify with `php bin/console debug:container monolog` if needed. Stop.
