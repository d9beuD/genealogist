# Users & Passwords (Symfony 8.1)

## User class

A user is any class implementing `UserInterface`; add
`PasswordAuthenticatedUserInterface` if it has a password. Usually a Doctrine
entity. Generate with `php bin/console make:user`.

```php
namespace App\Entity;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /** @var list<string> */
    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    // identifier used to load the user (must be unique)
    public function getUserIdentifier(): string { return (string) $this->email; }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER'; // every user gets ROLE_USER
        return array_unique($roles);
    }

    public function getPassword(): ?string { return $this->password; }
    public function setPassword(string $password): static { $this->password = $password; return $this; }
}
```

After creating/changing the entity: `php bin/console make:migration` then
`php bin/console doctrine:migrations:migrate`.

## User providers

Loads/reloads users by identifier. Built in: entity, ldap, memory, chain.

```yaml
security:
    providers:
        app_user_provider:
            entity:
                class: App\Entity\User
                property: email
```

Service ID pattern: `security.user.provider.concrete.<name>`. With a single
provider, autowire `UserProviderInterface`.

## Password hashing config

```yaml
security:
    password_hashers:
        # auto-selects best algorithm (currently bcrypt) and migrates over time
        Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface: 'auto'
        # or per class with options:
        App\Entity\User:
            algorithm: 'auto'
            cost: 15
```

## Hashing a password (registration)

```php
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

public function register(UserPasswordHasherInterface $passwordHasher): Response
{
    $user = new User();
    $hashed = $passwordHasher->hashPassword($user, $plaintextPassword);
    $user->setPassword($hashed);
    // persist $user ...
}
```

Hash from CLI: `php bin/console security:hash-password` (Symfony 8.1 can read the
password from stdin: `echo $PASSWORD | php bin/console security:hash-password --no-interaction -`).

## Password migration / rehashing

With `'auto'`, passwords are upgraded to newer algorithms on successful login.
For a Doctrine entity that stores hashed passwords, the related repository must
implement `Symfony\Component\Security\Core\User\PasswordUpgraderInterface` so the
new hash can be saved.

## User checkers (extra account checks)

Implement `Symfony\Component\Security\Core\User\UserCheckerInterface`
(`checkPreAuth` / `checkPostAuth`) to reject disabled/banned accounts during
authentication, then wire it on the firewall via `user_checker:`.
