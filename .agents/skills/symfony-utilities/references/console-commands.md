# Console Commands

Symfony 8.1. Commands are auto-registered services via the `#[AsCommand]` attribute. Place classes in `src/Command/`.

## 1. Minimal invokable command

```php
// src/Command/CreateUserCommand.php
namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

// the name is what users type after "php bin/console"
#[AsCommand(name: 'app:create-user', description: 'Creates a new user.')]
class CreateUserCommand
{
    public function __invoke(): int
    {
        // ... put your logic here

        return Command::SUCCESS;   // int(0)
        // return Command::FAILURE; // int(1) on error
        // return Command::INVALID; // int(2) on bad usage
    }
}
```

Run it: `php bin/console app:create-user`. Done when this class exists and returns an int.

Add help/usages/aliases inside the attribute:

```php
#[AsCommand(
    name: 'app:create-user|app:add-user', // first is real name, rest are aliases
    description: 'Creates a new user.',
    help: 'This command allows you to create a user...',
    usages: ['bob', 'alice --as-admin'],
)]
```

## 2. Inject services (dependency injection)

The command is already a service. Type-hint dependencies in the constructor:

```php
use App\Service\UserManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

#[AsCommand(name: 'app:create-user')]
class CreateUserCommand
{
    public function __construct(
        private UserManager $userManager,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(#[Argument] string $username): int
    {
        $this->userManager->create($username);
        $this->logger->info('User created');

        return Command::SUCCESS;
    }
}
```

## 3. Arguments and options (invokable style)

Use `#[Argument]` and `#[Option]` on `__invoke()` parameters.

```php
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;

#[AsCommand(name: 'app:greet')]
class GreetCommand
{
    public function __invoke(
        // required argument: no default value
        #[Argument(description: 'The username')]
        string $name,

        // optional argument: has a default value
        #[Argument]
        string $lastName = '',

        // array argument: list of values
        #[Argument]
        array $extra = [],

        // option that accepts a value: --iterations=5
        #[Option(description: 'Number of iterations', shortcut: 'i')]
        int $iterations = 1,

        // boolean flag: --yell
        #[Option]
        bool $yell = false,

        // negatable flag: --color / --no-color
        #[Option]
        ?bool $color = null,
    ): int {
        // ...
        return Command::SUCCESS;
    }
}
```

Mode is inferred from the parameter type/default:
- Argument: required = no default; optional = has default; array = `array` type.
- Option: bool default `false` = flag (`VALUE_NONE`); bool default `true` or `?bool null` = negatable; `string|int|float` = value required; `array` = repeatable; `string|bool $x = false` = optional value.
- `BackedEnum` types are auto-converted from the input string.

Name defaults to kebab-case of the parameter (`$lastName` → `last-name`).

## 4. Styled output with SymfonyStyle

Type-hint `SymfonyStyle $io` in `__invoke()` to get the helper API.

```php
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:my-command')]
class MyCommand
{
    public function __invoke(SymfonyStyle $io): int
    {
        $io->title('Lorem Ipsum');
        $io->section('Adding a User');
        $io->text(['line one', 'line two']);
        $io->listing(['item 1', 'item 2']);
        $io->table(['Header 1', 'Header 2'], [['c1', 'c2'], ['c3', 'c4']]);
        $io->note('A highlighted note.');
        $io->caution('Dangerous!');

        // result blocks (use once, at the end):
        $io->success('Done.');   // green bg + [OK]
        $io->info('FYI.');       // green bg, no label
        $io->warning('Heads up.');
        $io->error('Failed.');

        // progress bar
        $io->progressStart(100);
        // ... $io->progressAdvance();
        $io->progressFinish();

        $io->newLine();

        return Command::SUCCESS;
    }
}
```

Raw output (no styling): type-hint `OutputInterface $output` and use `$output->writeln('...')` / `$output->write('...')`. Use `<info>...</>` tags for color.

## 5. Verbosity levels

Run with `-q`/`--quiet`, `-v`, `-vv`, `-vvv`, or `--silent`. Branch on it:

```php
use Symfony\Component\Console\Output\OutputInterface;

public function __invoke(OutputInterface $output): int
{
    if ($output->isVerbose()) {
        $output->writeln('Extra detail');
    }

    // or pass the level constant
    $output->writeln('verbose only', OutputInterface::VERBOSITY_VERBOSE);

    return Command::SUCCESS;
}
```

Methods: `isSilent()`, `isQuiet()`, `isVerbose()`, `isVeryVerbose()`, `isDebug()`.

## 6. Lifecycle hooks (only if extending Command)

To use `initialize()` / `interact()`, extend `Command`:

```php
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:create-user')]
class CreateUserCommand extends Command
{
    public function interact(InputInterface $input, OutputInterface $output): void
    {
        // ask the user for missing required input (skipped with --no-interaction)
    }

    public function __invoke(/* ... */): int
    {
        return Command::SUCCESS;
    }
}
```

## 7. Testing a command (Symfony 8.1 runCommand)

```php
// tests/Command/CreateUserCommandTest.php
namespace App\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CreateUserCommandTest extends KernelTestCase
{
    public function testExecute(): void
    {
        $result = static::runCommand('app:create-user', [
            'username' => 'Wouter',   // argument
            // '--some-option' => true,  // VALUE_NONE option needs true
        ]);

        $this->assertCommandIsSuccessful($result);
        $this->assertStringContainsString('Username: Wouter', $result->getOutput());
    }
}
```

`$result` exposes `getOutput()` (stdout), `getErrorOutput()` (stderr), `getDisplay()` (combined), `statusCode`. Pass interactive answers as the 3rd argument: `static::runCommand('app:create-user', [], ['Wouter', 'yes']);`.

STOP once the command class is written and returns an int (and, if asked, the test asserts success).
