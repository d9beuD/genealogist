# CSRF & Firewall structure (Symfony 8.1)

## CSRF on the login form

Login forms are NOT CSRF-protected by default. Two steps:

1. Enable it on the authenticator:

```yaml
security:
    firewalls:
        main:
            form_login:
                enable_csrf: true
```

2. Add the hidden token to the template. The field must be named `_csrf_token`
   and the token id must be `authenticate`:

```twig
<form action="{{ path('app_login') }}" method="post">
    {# username/password fields #}
    <input type="hidden" name="_csrf_token"
           data-controller="csrf-protection"
           value="{{ csrf_token('authenticate') }}">
    <button type="submit">login</button>
</form>
```

Rename the field with `csrf_parameter` and the token id with `csrf_token_id`
under `form_login`.

## CSRF on regular (non-login) forms

Symfony Forms include CSRF protection automatically. For manual forms, output
`{{ csrf_token('a_token_id') }}` in a hidden field and validate server-side with
`isCsrfTokenValid('a_token_id', $request->request->get('token'))`.

## Firewall structure rules

- Order matters: the FIRST firewall whose `pattern` matches handles the request.
- Keep the fake `dev` firewall first (so profiler/assets are never blocked) and
  the catch-all `main` (no `pattern`) last.

```yaml
firewalls:
    dev:
        pattern: ^/(_profiler|_wdt|assets|build)/
        security: false
    main:
        lazy: true
        provider: app_user_provider
```

`pattern` is a regex without delimiters. You can pass an array of regexes instead
of one long pattern. Firewalls can also be restricted by host or other matchers.

## Forcing HTTPS

Per access_control entry: if the channel is `http`, the user is redirected to `https`.

```yaml
security:
    access_control:
        - { path: ^/cart/checkout, roles: PUBLIC_ACCESS, requires_channel: https }
```

## Restrict by IP / port

`ips` means "this entry only matches these IPs" — non-matching requests continue
to the next access_control entry (it does not by itself forbid other IPs).

```yaml
access_control:
    - { path: ^/admin, roles: ROLE_USER, ips: [127.0.0.1, ::1] }
    - { path: ^/cart/checkout, roles: PUBLIC_ACCESS, port: 8080 }
```

## Getting the matched firewall config

```php
use Symfony\Bundle\SecurityBundle\Security;

$name = $security->getFirewallConfig($request)?->getName();
```

Do not call `getFirewallConfig()` in a constructor (auth may not be complete);
store the `Security` service and call it at request time.
