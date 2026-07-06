# Platform component

Unified interface to AI models/providers (OpenAI, Anthropic, Gemini, Azure, Mistral, Ollama, ...).

Install:

```terminal
composer require symfony/ai-platform
```

## Create a platform (provider factory)

Each provider ships a `Factory`. Namespace pattern: `Symfony\AI\Platform\Bridge\<Provider>\Factory`.

```php
use Symfony\AI\Platform\Bridge\OpenAi\Factory;

$platform = Factory::createPlatform($apiKey); // returns Symfony\AI\Platform\PlatformInterface
```

Other bridges, same pattern:
`Bridge\Anthropic\Factory`, `Bridge\Gemini\Factory`, `Bridge\Mistral\Factory`,
`Bridge\Ollama\Factory` (pass an endpoint URL, e.g. `Factory::createPlatform('http://127.0.0.1:11434')`).

## Invoke a model

```php
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;

// Text completion -> Symfony\AI\Platform\Result\TextResult
$result = $platform->invoke('gpt-4o-mini', new MessageBag(
    Message::forSystem('You are a pirate and you write funny.'),
    Message::ofUser('What is the Symfony framework?'),
));
echo $result->asText();

// Embedding vector -> Symfony\AI\Platform\Result\VectorResult
$vectorResult = $platform->invoke('text-embedding-3-small', 'What is the capital of France?');
```

`MessageBag` holds the conversation. Builders: `Message::forSystem(...)`, `Message::ofUser(...)`.

## Models, capabilities, options

`Symfony\AI\Platform\Model` = name + capabilities + options. Pass a model name string, or a
bridge-specific subclass instance (NOT the base `Model`):

```php
use Symfony\AI\Platform\Bridge\OpenAi\Gpt;
use Symfony\AI\Platform\Capability;

$model = new Gpt('gpt-newest', [
    Capability::INPUT_MESSAGES,
    Capability::OUTPUT_TEXT,
    Capability::TOOL_CALLING,
], ['temperature' => 0.5]);

$result = $platform->invoke($model, new MessageBag(Message::ofUser('...')));
```

Capabilities are constants on `Symfony\AI\Platform\Capability` (e.g. `INPUT_AUDIO`, `OUTPUT_IMAGE`,
`THINKING`, `OUTPUT_STREAMING`, `OUTPUT_STRUCTURED`). Options like `temperature`, `max_output_tokens`.

## Generic / OpenAI-compatible providers (LiteLLM, OpenRouter)

```php
use Symfony\AI\Platform\Bridge\Generic\Factory;

$platform = Factory::createPlatform('https://api.example.com', 'sk-xxxxxx', $httpClient, $modelCatalog);
$result = $platform->invoke('model-name', $messages);
```

Requires a `Symfony\AI\Platform\Bridge\Generic\ModelCatalog` using `CompletionsModel` / `EmbeddingsModel`.

## Multi-provider routing

```php
use Symfony\AI\Platform\Bridge\Anthropic\Factory as AnthropicFactory;
use Symfony\AI\Platform\Bridge\OpenAi\Factory as OpenAiFactory;
use Symfony\AI\Platform\Platform;

$platform = new Platform([
    OpenAiFactory::createProvider(apiKey: $openAiKey),
    AnthropicFactory::createProvider(apiKey: $anthropicKey),
]);
$platform->invoke('gpt-4o', $messages);            // -> OpenAI
$platform->invoke('claude-3-5-sonnet', $messages); // -> Anthropic
```

Default router (`CatalogBasedModelRouter`) sends each model to the first provider whose catalog knows it.

Done: `$platform->invoke(...)` returns a result. Stop. For agents/tools open `agent-tools.md`.
