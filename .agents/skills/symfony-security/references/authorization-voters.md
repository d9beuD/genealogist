# Authorization & Voters (Symfony 8.1)

Authorization decides what an authenticated user may do. Use roles for coarse
access, voters for object-level rules. Roles MUST start with `ROLE_`.

## access_control (security.yaml)

Only the FIRST matching entry is applied. Matching options: `path` (regex, no
delimiters), `ip`/`ips`, `port`, `host`, `methods`, `route`, `attributes`,
`request_matcher`. Enforcement options: `roles`, `allow_if`, `requires_channel`.

```yaml
security:
    access_control:
        - { path: ^/admin, roles: ROLE_ADMIN }
        - { path: ^/profile, roles: ROLE_USER }
        # only matches from these IPs; others fall through to next rule
        - { path: ^/internal, roles: PUBLIC_ACCESS, ips: [127.0.0.1, ::1] }
        - { path: ^/internal, roles: ROLE_NO_ACCESS }   # trick: never-granted role = deny
```

URI matching ignores `$_GET`. `PUBLIC_ACCESS` allows everyone; an invented role
nobody has (e.g. `ROLE_NO_ACCESS`) denies everyone.

Expression with `allow_if` (ORs with `roles` under the default `affirmative` strategy):

```yaml
access_control:
    - path: ^/_internal/secure
      roles: 'ROLE_ADMIN'
      allow_if: "'127.0.0.1' == request.getClientIp() or request.headers.has('X-Secure-Access')"
```

## role_hierarchy

```yaml
security:
    role_hierarchy:
        ROLE_ADMIN:       ROLE_USER
        ROLE_SUPER_ADMIN: [ROLE_ADMIN, ROLE_ALLOWED_TO_SWITCH]
```

For hierarchy to apply, check roles via the security methods, NOT `$user->getRoles()`:

```php
$hasAccess = $this->isGranted('ROLE_ADMIN');        // GOOD
$this->denyAccessUnlessGranted('ROLE_ADMIN');       // GOOD
// BAD: in_array('ROLE_ADMIN', $user->getRoles())
```

## Protecting controllers

```php
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
public function adminArea(): Response { /* ... */ }

// object-level: 2nd arg is the controller argument name passed to the voter
#[IsGranted('edit', 'post')]
public function edit(Post $post): Response { /* ... */ }
```

Or imperatively (throws `AccessDeniedException` -> 403, or redirects to login if anonymous):

```php
$this->denyAccessUnlessGranted('edit', $post);
```

Customize message/status: `#[IsGranted('show', 'post', 'Post not found', 404)]`.

Auth state attributes also work: `IS_AUTHENTICATED_FULLY`, `IS_AUTHENTICATED_REMEMBERED`.

## Voters (object-level permissions)

Every voter is called by `isGranted()` / `denyAccessUnlessGranted` / access_control.
Extend `Voter`; with default `services.yaml` it is auto-registered (tag
`security.voter`) — no manual config needed.

```php
namespace App\Security;

use App\Entity\Post;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PostVoter extends Voter
{
    const VIEW = 'view';
    const EDIT = 'edit';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT]) && $subject instanceof Post;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            $vote?->addReason('The user is not logged in.');
            return false;
        }
        /** @var Post $post */
        $post = $subject;

        return match ($attribute) {
            self::VIEW => !$post->isPrivate() || $user === $post->getAuthor(),
            self::EDIT => $user === $post->getAuthor(),
            default => throw new \LogicException('Unsupported attribute.'),
        };
    }
}
```

To always allow super admins inside a voter, inject `AccessDecisionManagerInterface`
and call `$this->accessDecisionManager->decide($token, ['ROLE_SUPER_ADMIN'])` —
do NOT use `Security::isGranted()` inside a voter (it may use a different token).

Priority (Symfony 8.1): add `#[AsTaggedItem(priority: 10)]`
(`Symfony\Component\DependencyInjection\Attribute\AsTaggedItem`).

## Access decision strategy

```yaml
security:
    access_decision_manager:
        strategy: unanimous   # affirmative (default) | consensus | unanimous | priority
        allow_if_all_abstain: false
```

`affirmative`: granted if any voter grants. `unanimous`: granted unless any voter
denies. Use `unanimous` when multiple voters must all agree.
