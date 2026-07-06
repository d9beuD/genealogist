# AI Bundle (config/packages/ai.yaml)

Symfony integration wiring Platform, Agent, Chat, Store via DI. Install:

```terminal
composer require symfony/ai-bundle
# minimal Symfony quick start (bundle + agent):
composer require symfony/ai-bundle symfony/ai-agent
```

## Minimal config

```yaml
# config/packages/ai.yaml
ai:
    platform:
        openai:
            api_key: '%env(OPENAI_API_KEY)%'
    agent:
        default:
            model: 'gpt-4o-mini'
```

Then inject `Symfony\AI\Agent\AgentInterface` and call `$agent->call($messageBag)`.

## Platforms

```yaml
ai:
    platform:
        anthropic:
            api_key: '%env(ANTHROPIC_API_KEY)%'
        gemini:
            api_key: '%env(GEMINI_API_KEY)%'
        mistral:
            api_key: '%env(MISTRAL_API_KEY)%'
        ollama:
            endpoint: '%env(OLLAMA_HOST_URL)%'
        azure:
            gpt_deployment:                       # multiple deployments possible
                base_url: '%env(AZURE_OPENAI_BASEURL)%'
                deployment: '%env(AZURE_OPENAI_GPT)%'
                api_key: '%env(AZURE_OPENAI_KEY)%'
                api_version: '%env(AZURE_GPT_VERSION)%'
```

Each platform is exposed as a service id `ai.platform.<name>` (e.g. `ai.platform.anthropic`,
`ai.platform.azure.gpt_deployment`).

## Agents (incl. tools)

```yaml
ai:
    agent:
        rag:
            platform: 'ai.platform.azure.gpt_deployment'
            model: 'gpt-4o-mini'
            memory: 'You have access to conversation history and user preferences' # optional static memory
            prompt:
                text: 'You are a helpful assistant that can answer questions.'
                include_tools: true        # append tool definitions to the system prompt
            tools:
                # service that carries #[AsTool]
                - 'Symfony\AI\Agent\Bridge\SimilaritySearch\SimilaritySearch'
                # service WITHOUT #[AsTool]
                - service: 'App\Agent\Tool\CompanyName'
                  name: 'company_name'
                  description: 'Provides the name of your company'
                  method: 'foo'            # optional, default '__invoke'
                # another agent used as a tool
                - agent: 'research'
                  name: 'wikipedia_research'
                  description: 'Can research on Wikipedia'
        research:
            platform: 'ai.platform.anthropic'
            model: 'claude-3-7-sonnet-latest'
            tools:                          # tools are opt-in; omit = no tools; "tools: true" = all tools
                - 'Symfony\AI\Agent\Bridge\Wikipedia\Wikipedia'
            fault_tolerant_toolbox: false   # default true
            max_tool_calls: 75              # default 50; null disables the cap
        search_agent:
            platform: 'ai.platform.perplexity'
            model: 'sonar'
            tools: false
```

## Stores, vectorizers, indexers (RAG)

```yaml
ai:
    store:
        chromadb:
            default:
                collection: 'my_collection'
        cache:
            research:
                service: 'cache.app'
                cache_key: 'research'
                strategy: 'chebyshev'
    vectorizer:
        openai_embeddings:
            platform: 'ai.platform.openai'
            model:
                name: 'text-embedding-3-small'
                options:
                    dimensions: 512
        mistral_embeddings:
            platform: 'ai.platform.mistral'
            model: 'mistral-embed'
    indexer:
        default:                                          # DocumentIndexer
            vectorizer: 'ai.vectorizer.openai_embeddings'
            store: 'ai.store.chromadb.default'
        research:                                         # SourceIndexer
            loader: 'Symfony\AI\Store\Document\Loader\TextFileLoader'
            vectorizer: 'ai.vectorizer.mistral_embeddings'
            store: 'ai.store.memory.research'
        docs:                                             # ConfiguredSourceIndexer
            loader: 'Symfony\AI\Store\Document\Loader\RstToctreeLoader'
            source: '/path/to/docs/index.rst'
            vectorizer: 'ai.vectorizer.openai_embeddings'
            store: 'ai.store.chromadb.default'
            transformers:
                - 'Symfony\AI\Store\Document\Transformer\TextSplitTransformer'
            filters: []
```

Service ids: `ai.vectorizer.<name>`, `ai.store.<type>.<name>`, `ai.indexer.<name>`.

## Generic / cached platforms

Generic (OpenAI-compatible, e.g. LiteLLM): `platform.generic.<name>` with `base_url`, `api_key`,
`model_catalog`. Cache decorator:

```yaml
ai:
    platform:
        openai:
            api_key: '%env(OPENAI_API_KEY)%'
        cache:
            openai:
                platform: 'ai.platform.openai'
                service: 'cache.app'
    agent:
        openai:
            platform: 'ai.platform.cache.openai'
            model: 'gpt-4o-mini'
```

## Store DI aliases

Each configured store gets two autowire aliases: `StoreInterface $storeName` and the type-prefixed
`StoreInterface $typeStoreName` (camelCase).

Done: YAML validates and the injected agent/store works. Stop.
