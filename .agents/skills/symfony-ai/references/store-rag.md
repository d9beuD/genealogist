# Store component + RAG

Vector storage abstraction for Retrieval Augmented Generation. Install:

```terminal
composer require symfony/ai-store
```

RAG flow: load documents -> vectorize (embeddings) -> store -> retrieve similar -> feed agent.

## Indexing

All indexers implement `Symfony\AI\Store\IndexerInterface` and share `index()`. Pipeline inside
`DocumentProcessor`: filter -> transform -> vectorize -> store.

DocumentIndexer (pass documents directly):

```php
use Symfony\AI\Store\Document\TextDocument;
use Symfony\AI\Store\Document\Vectorizer;
use Symfony\AI\Store\Indexer\DocumentIndexer;
use Symfony\AI\Store\Indexer\DocumentProcessor;

$vectorizer = new Vectorizer($platform, $model);            // $model = embeddings model
$indexer = new DocumentIndexer(new DocumentProcessor($vectorizer, $store));
$indexer->index(new TextDocument('id-1', 'This is a sample document.'));
```

SourceIndexer (load from a path/URL via a loader):

```php
use Symfony\AI\Store\Document\Loader\TextFileLoader;
use Symfony\AI\Store\Indexer\SourceIndexer;

$indexer = new SourceIndexer(new TextFileLoader(), new DocumentProcessor($vectorizer, $store));
$indexer->index('/path/to/document.txt');
// or: $indexer->index(['/path/to/doc1.txt', '/path/to/doc2.txt']);
```

ConfiguredSourceIndexer wraps a SourceIndexer with a default source: `$indexer->index();`.

## Document loaders

Under `Symfony\AI\Store\Document\Loader\`: `CsvLoader`, `DirectoryLoader`, `InMemoryLoader`,
`JsonFileLoader`, `MarkdownLoader`, `RssFeedLoader`, `RstLoader`, `RstToctreeLoader`, `TextFileLoader`.

```php
use Symfony\AI\Store\Document\Loader\DirectoryLoader;
use Symfony\AI\Store\Document\Loader\MarkdownLoader;
use Symfony\AI\Store\Document\Loader\TextFileLoader;

$loader = new DirectoryLoader([
    'md'  => new MarkdownLoader(),
    'txt' => new TextFileLoader(),
], recursive: false);
$documents = $loader->load('/path/to/directory');
```

Custom loader: implement `Symfony\AI\Store\Document\LoaderInterface::load(?string $source = null, array $options = []): iterable`.

## Retrieving

```php
use Symfony\AI\Store\Retriever;

$retriever = new Retriever($store, $vectorizer);
$documents = $retriever->retrieve('What is the capital of France?');
foreach ($documents as $document) {
    echo $document->metadata->get('source');
}
```

## RAG with an agent (SimilaritySearch tool)

The Agent component ships the built-in tool `Symfony\AI\Agent\Bridge\SimilaritySearch\SimilaritySearch`.

```php
use Symfony\AI\Agent\Agent;
use Symfony\AI\Agent\Bridge\SimilaritySearch\SimilaritySearch;
use Symfony\AI\Agent\Toolbox\AgentProcessor;
use Symfony\AI\Agent\Toolbox\Toolbox;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Store\Retriever;

$retriever = new Retriever($store, $vectorizer);
$toolbox = new Toolbox([new SimilaritySearch($retriever)]);
$processor = new AgentProcessor($toolbox);
$agent = new Agent($platform, $model, [$processor], [$processor]);

$messages = new MessageBag(
    Message::forSystem('Answer questions only using the similarity_search tool. If you cannot find an answer, say so.'),
    Message::ofUser('...'),
);
$result = $agent->call($messages);
```

## Supported stores (selection)

InMemory (testing), Chroma, MongoDB Atlas, Pinecone, Postgres, Qdrant, Redis, SQLite, Supabase,
Weaviate, Elasticsearch, Meilisearch, Milvus, Neo4j, Azure AI Search, S3 Vectors, Symfony Cache.
Some need extras (e.g. `mongodb/mongodb`, `probots-io/pinecone-php`, `codewithkyrian/chromadb-php`).
InMemory and PSR-6 cache stores hold all data in PHP memory — testing only.

Done: documents indexed and the agent answers from the store. Stop. For YAML wiring open `ai-bundle.md`.
