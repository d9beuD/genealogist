# Bundles, Kernel & Configuration (Symfony 8.1)

## Bundles

A bundle is a reusable plugin. Core features ship as bundles (FrameworkBundle, SecurityBundle, etc.). Do NOT organize your own application code into bundles — keep app code under `src/` (`App\`). Bundles are only for sharing code across apps.

Bundles are enabled per environment in `config/bundles.php`:

```php
// config/bundles.php
return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Symfony\Bundle\DebugBundle\DebugBundle::class => ['dev' => true],
    Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => ['dev' => true, 'test' => true],
];
```

With Symfony Flex, this file is updated automatically when you install/remove packages — usually do not edit it by hand.

Create a bundle (only when sharing across apps):

```php
// src/AcmeBlogBundle.php
namespace Acme\BlogBundle;

use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class AcmeBlogBundle extends AbstractBundle
{
}
```

Use `AbstractBundle` for modern bundles; extend `Bundle` only for backward compatibility.

## Configuration files

- `config/packages/*.yaml` — per-package config (framework, twig, doctrine, ...), can be overridden per environment via `config/packages/{env}/`.
- `config/services.yaml` — your service definitions and parameters.
- `config/bundles.php` — enabled bundles.

## Parameters

```yaml
# config/services.yaml
parameters:
    app.admin_email: 'admin@example.com'

services:
    App\Service\Mailer:
        arguments:
            $adminEmail: '%app.admin_email%'
```

Inject a parameter with an attribute instead of YAML:

```php
use Symfony\Component\DependencyInjection\Attribute\Autowire;

public function __construct(
    #[Autowire(param: 'kernel.project_dir')] private string $projectDir,
) {}
```

## Environment variables

Reference env vars with `%env(VAR_NAME)%`:

```yaml
# config/packages/framework.yaml
framework:
    secret: '%env(APP_SECRET)%'
```

Default values live in `.env` (committed); per-machine overrides go in `.env.local` (not committed).

### Env-var processors

Cast/transform env vars with a processor prefix `%env(processor:VAR)%`:

```yaml
parameters:
    db_port: '%env(int:DATABASE_PORT)%'
    debug:   '%env(bool:APP_DEBUG)%'
    hosts:   '%env(json:ALLOWED_HOSTS)%'
    secret:  '%env(file:SECRET_FILE)%'
```

Common processors: `string`, `bool`, `int`, `float`, `json`, `file`, `resolve`, `default`, `csv`, `trim`. Also usable via `#[Autowire(env: 'bool:APP_DEBUG')]`.

## Secrets (vault)

For sensitive values, use the encrypted secrets store (requires the Sodium extension). Each environment has its own keys.

```bash
php bin/console secrets:generate-keys
php bin/console secrets:set DATABASE_PASSWORD
php bin/console secrets:list --reveal
```

Secrets are exposed as env vars, so reference them the same way: `%env(DATABASE_PASSWORD)%`. A local override can be set with `secrets:set --local`.

## Verify (run once)

- `php bin/console debug:container --parameters`
- `php bin/console debug:container --env-vars`
- `php bin/console config:dump-reference <bundle>`
