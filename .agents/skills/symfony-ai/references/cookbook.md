# Cookbook recipes

Pick ONE recipe below. Do not combine recipes unless the user asks.

## Chatbot with memory (Chat component)

Install: `composer require symfony/ai-chat`

```php
use Symfony\AI\Agent\Agent;
use Symfony\AI\Chat\Chat;
use Symfony\AI\Chat\InMemory\Store as InMemoryStore;
use Symfony\AI\Platform\Bridge\OpenAi\Factory;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;

$platform = Factory::createPlatform($apiKey);
$agent = new Agent($platform, 'gpt-4o-mini');
$chat = new Chat($agent, new InMemoryStore());

$chat->initiate(new MessageBag(Message::forSystem('You are a helpful assistant.')));
$chat->submit(Message::ofUser('Hello'));
```

`Chat` needs an `AgentInterface` plus a message store (implements `MessageStoreInterface` /
`ManagedStoreInterface`). Stores: InMemory, Cache, Doctrine DBAL, Redis, MongoDb, HttpFoundation
session, etc. For longer-term recall use a persistent store instead of `InMemoryStore`.

Streaming (do NOT use streaming with the Session message store):

```php
use Symfony\AI\Platform\Result\Stream\Delta\TextDelta;

foreach ($chat->stream(Message::ofUser('Tell me a story')) as $delta) {
    if ($delta instanceof TextDelta) {
        echo $delta;
    }
}
```

After the stream is consumed, user + assistant messages are persisted automatically.

(For injected system-prompt memory instead of stored history, use `MemoryInputProcessor` +
`StaticMemoryProvider` / `EmbeddingProvider` — see `agent-tools.md`.)

## Multi-agent orchestration (handoff)

Build specialist `Agent`s, then route via `Handoff` + `MultiAgent`.

```php
use Symfony\AI\Agent\MultiAgent\Handoff;
use Symfony\AI\Agent\MultiAgent\MultiAgent;

$handoffs = [
    new Handoff(to: $technical, when: ['bug', 'error', 'exception', 'technical']),
    new Handoff(to: $billing,   when: ['invoice', 'payment', 'billing', 'subscription']),
];

$multiAgent = new MultiAgent(
    orchestrator: $orchestrator,   // an Agent that decides routing
    handoffs: $handoffs,
    fallback: $fallback,           // an Agent for non-matching questions
);
```

`when` is a keyword array; the orchestrator delegates to the first matching agent, else the fallback.

## Human-in-the-loop (tool approval)

Install: `composer require symfony/ai-platform symfony/ai-agent symfony/event-dispatcher`

The toolbox dispatches `Symfony\AI\Agent\Toolbox\Event\ToolCallRequested` before each tool runs.
A listener can allow it (do nothing), deny it (`$event->deny($reason)`), or replace the result
(`$event->setResult($result)`).

```php
use Symfony\AI\Agent\Toolbox\Event\ToolCallRequested;
use Symfony\AI\Agent\Toolbox\Toolbox;
use Symfony\Component\EventDispatcher\EventDispatcher;

$dispatcher = new EventDispatcher();
$dispatcher->addListener(ToolCallRequested::class, function (ToolCallRequested $event): void {
    $toolCall = $event->getToolCall();
    echo \sprintf("Tool '%s' args: %s\nAllow? [y/N] ",
        $toolCall->getName(), json_encode($toolCall->getArguments()));
    if ('y' !== strtolower(trim(fgets(\STDIN)))) {
        $event->deny('User denied tool execution.');
    }
});

$toolbox = new Toolbox($tools, eventDispatcher: $dispatcher);
```

For selective approval, add a policy that returns Allow / Deny / AskUser per `ToolCall`
(e.g. auto-allow read-only tools by name pattern) and call it inside the listener.

Done: the recipe runs end-to-end (chat responds / routing works / tool approval gates execution). Stop.
