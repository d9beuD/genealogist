# Agent component + tools

Agents sit on top of Platform (and Store). Install:

```terminal
composer require symfony/ai-agent
```

## Basic agent

```php
use Symfony\AI\Agent\Agent;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;

$agent = new Agent($platform, 'gpt-4o-mini'); // implements Symfony\AI\Agent\AgentInterface

$messages = new MessageBag(
    Message::forSystem('You are a helpful chatbot.'),
    Message::ofUser('Hello, how are you?'),
);
$result = $agent->call($messages);
echo $result->getContent();
```

## Enabling tools

```php
use Symfony\AI\Agent\Toolbox\AgentProcessor;
use Symfony\AI\Agent\Toolbox\Toolbox;

$toolbox = new Toolbox([new YourTool()]);
$toolProcessor = new AgentProcessor($toolbox);

$agent = new Agent($platform, $model,
    inputProcessors: [$toolProcessor],
    outputProcessors: [$toolProcessor],
);
```

Tool-call iterations are capped at 50 per call (throws `MaxIterationsExceededException`). Change it:

```php
$toolProcessor = new AgentProcessor($toolbox, maxToolCalls: 75);  // raise
$toolProcessor = new AgentProcessor($toolbox, maxToolCalls: null); // unbounded
```

## Defining a tool

Any class + the `#[AsTool]` attribute. Return value must be a string; arrays/`JsonSerializable`
objects are auto-converted to JSON.

```php
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

#[AsTool('company_name', 'Provides the name of your company')]
final class CompanyName
{
    public function __invoke(): string
    {
        return 'ACME Corp.';
    }
}
```

Multiple tools per class via `method:`:

```php
#[AsTool(name: 'weather_current', description: 'get current weather', method: 'current')]
#[AsTool(name: 'weather_forecast', description: 'get weather forecast', method: 'forecast')]
final readonly class OpenMeteo
{
    public function current(float $latitude, float $longitude): array { /* ... */ }
    public function forecast(float $latitude, float $longitude): array { /* ... */ }
}
```

## Tool parameters / JSON Schema

JSON Schema is generated from argument types + PHPDoc. Add validation hints with `#[Schema]`
(`Symfony\AI\Platform\Contract\JsonSchema\Attribute\Schema`). Note: hints are sent to the LLM, not
validated by Symfony AI.

```php
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;
use Symfony\AI\Platform\Contract\JsonSchema\Attribute\Schema;

#[AsTool('my_tool', 'Example tool with parameter requirements.')]
final class MyTool
{
    /**
     * @param string        $name       The name of an object
     * @param int           $number     The number of an object
     * @param array<string> $categories List of valid categories
     */
    public function __invoke(
        #[Schema(pattern: '/([a-z0-1]){5}/')] string $name,
        #[Schema(minimum: 0, maximum: 10)] int $number,
        #[Schema(enum: ['tech', 'business', 'science'])] array $categories,
    ): string { /* ... */ }
}
```

Use `#[Schema(ref: __DIR__.'/schema.json')]` to load a schema from file (no other Schema args then).

## Tools on classes you cannot annotate

Register explicitly via `MemoryToolFactory`:

```php
use Symfony\AI\Agent\Toolbox\ToolFactory\MemoryToolFactory;

$metadataFactory = (new MemoryToolFactory())
    ->addTool(Clock::class, 'clock', 'Get the current date and time', 'now');
```

## Agent memory

```php
use Symfony\AI\Agent\Memory\MemoryInputProcessor;
use Symfony\AI\Agent\Memory\StaticMemoryProvider;

$facts = new StaticMemoryProvider(
    'The user is allergic to nuts',
    'The user prefers brief explanations',
);
$memoryProcessor = new MemoryInputProcessor([$facts]);
$agent = new Agent($platform, $model, [$memoryProcessor]);
```

`EmbeddingProvider($platform, $embeddings, $store)` injects vector-retrieved context.
Disable per call: `$agent->call($messages, ['use_memory' => false]);`

## Testing

`Symfony\AI\Agent\MockAgent` returns canned answers and exposes `assertCallCount()`,
`assertCalledWith()`, `getCalls()`.

Done: agent returns a result with the tool wired. Stop. For RAG open `store-rag.md`.
