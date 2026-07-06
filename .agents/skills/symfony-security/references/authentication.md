# Authentication (Symfony 8.1)

Install: `composer require symfony/security-bundle`. Config lives in
`config/packages/security.yaml`. Only ONE firewall is active per request; it is
chosen by the first matching `pattern`. The `dev` firewall must come first and
the catch-all `main` (no `pattern`) must come last.

## Firewall + provider skeleton

```yaml
# config/packages/security.yaml
security:
    providers:
        app_user_provider:
            entity:
                class: App\Entity\User
                property: email
    firewalls:
        dev:
            pattern: ^/(_profiler|_wdt|assets|build)/
            security: false
        main:
            lazy: true
            provider: app_user_provider
            # add ONE authenticator key below (form_login, json_login, http_basic, ...)
```

`lazy: true` avoids starting the session until authorization is actually needed.

## Form login

Create a login route/controller, then enable `form_login`:

```yaml
firewalls:
    main:
        form_login:
            login_path: app_login   # route name or URL
            check_path: app_login
```

Controller renders the form and reads errors:

```php
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/login', name: 'app_login')]
public function index(AuthenticationUtils $authenticationUtils): Response
{
    $error = $authenticationUtils->getLastAuthenticationError();
    $lastUsername = $authenticationUtils->getLastUsername();

    return $this->render('login/index.html.twig', [
        'last_username' => $lastUsername,
        'error' => $error,
    ]);
}
```

Template fields must be named `_username` and `_password`, POSTing to `check_path`.
Display errors with `error.messageKey|trans(error.messageData, 'security')` —
never `error.message`. For CSRF on login see `csrf-firewalls.md`.

## JSON login (APIs)

```yaml
firewalls:
    main:
        json_login:
            check_path: api_login   # route name; client POSTs JSON {username, password}
```

Return the authenticated user from the controller:

```php
use App\Entity\User;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/login', name: 'api_login', methods: ['POST'])]
public function index(#[CurrentUser] ?User $user): Response
{
    if (null === $user) {
        return $this->json(['message' => 'missing credentials'], Response::HTTP_UNAUTHORIZED);
    }
    $token = '...'; // create an API token for $user
    return $this->json(['user' => $user->getUserIdentifier(), 'token' => $token]);
}
```

## HTTP basic

```yaml
firewalls:
    main:
        http_basic:
            realm: Secured Area
```

Note: logout does not work with `http_basic` (browser keeps sending credentials).

## Login throttling (brute-force protection)

Requires `composer require symfony/rate-limiter`.

```yaml
firewalls:
    main:
        login_throttling: null            # 5 attempts/minute default
        # or:
        login_throttling:
            max_attempts: 3
            interval: '15 minutes'
```

## Logout

```yaml
firewalls:
    main:
        logout:
            path: app_logout   # route name or URL; reference route _logout_main
            # target: app_homepage
```

Run custom logic via a `LogoutEvent` subscriber, or programmatically:

```php
use Symfony\Bundle\SecurityBundle\Security;
$response = $security->logout();      // logs out current firewall
```

## Fetching the current user

In a controller, prefer the attribute (nullable = anonymous allowed, non-nullable
= auto 403):

```php
use App\Entity\User;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

public function index(#[CurrentUser] User $user): Response { /* ... */ }
```

In a service inject `Symfony\Bundle\SecurityBundle\Security` and call `$security->getUser()`.

## Custom authenticator (e.g. API token)

Extend `AbstractAuthenticator` and return a Passport with a `UserBadge`:

```php
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class ApiKeyAuthenticator extends AbstractAuthenticator
{
    public function supports(Request $request): ?bool
    {
        return $request->headers->has('auth-token');
    }

    public function authenticate(Request $request): Passport
    {
        $apiToken = $request->headers->get('auth-token');
        if (null === $apiToken) {
            throw new CustomUserMessageAuthenticationException('No API token provided');
        }
        $userIdentifier = /* look up user by $apiToken */ '';
        return new SelfValidatingPassport(new UserBadge($userIdentifier));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null; // let the request continue
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse(
            ['message' => strtr($exception->getMessageKey(), $exception->getMessageData())],
            Response::HTTP_UNAUTHORIZED
        );
    }
}
```

Enable it with `custom_authenticators: [App\Security\ApiKeyAuthenticator]` under
the firewall. For password-based passports use
`new Passport(new UserBadge($identifier), new PasswordCredentials($plaintext))`.
