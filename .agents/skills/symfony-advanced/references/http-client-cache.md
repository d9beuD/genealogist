# HTTP Client + Cache

Two components. Pick the one the task needs.

---

# HTTP Client (call external APIs)

Symfony 8.1. Type-hint `HttpClientInterface` (autowired).
```php
use Symfony\Contracts\HttpClient\HttpClientInterface;

public function __construct(
    private HttpClientInterface $client,
) {
}

public function fetch(): void
{
    $response = $this->client->request(
        'GET',
        'https://api.example.com/products',
    );

    $statusCode = $response->getStatusCode();   // 200
    $contentType = $response->getHeaders()['content-type'][0] ?? null;
    $content = $response->getContent();          // raw string body
    $data = $response->toArray();                // decoded JSON array
}
```

## Request options
```php
$response = $this->client->request('POST', 'https://api.example.com/items', [
    'headers' => ['Authorization' => 'Bearer '.$token],
    'json'    => ['name' => 'Widget'],   // sets Content-Type: application/json
    'query'   => ['page' => 2],
]);
```

## Standalone (outside the framework)
```php
use Symfony\Component\HttpClient\HttpClient;

$client = HttpClient::create();
$client = HttpClient::create([], 10);          // max 10 concurrent connections
$response = $client->request('GET', 'https://example.com');
```
Apply shared options with `$client->withOptions([...])`.

## Scoped / base URI client
Use `ScopingHttpClient` to apply options (base_uri, headers) only to URLs matching a regexp:
```php
use Symfony\Component\HttpClient\ScopingHttpClient;

$client = ScopingHttpClient::forBaseUri(HttpClient::create(), 'https://api.example.com', [
    'headers' => ['Authorization' => 'Bearer '.$token],
]);
```

DONE when `getStatusCode()` / `toArray()` returns the expected data.

---

# Cache

Symfony 8.1. Default pools: `cache.app` (general purpose) and `cache.system`.

## Inject and use
Autowiring injects `cache.app` for `CacheInterface`, `CacheItemPoolInterface`, or `AdapterInterface`.
```php
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

public function __construct(
    private CacheInterface $cache,
) {
}

public function value(): string
{
    // callback runs ONLY on cache miss; result is stored
    $value = $this->cache->get('my_cache_key', function (ItemInterface $item): string {
        $item->expiresAfter(3600);
        return $this->computeExpensiveValue();
    });

    return $value;
}
```
Delete: `$this->cache->delete('my_cache_key');`

## Custom pools
`config/packages/cache.yaml`:
```yaml
framework:
    cache:
        pools:
            # autowireable via "CacheInterface $myCachePool"
            my.cache_pool:
                adapter: cache.app
```
Inject a named pool by camelCasing the pool name: argument `CacheInterface $myCachePool`.

DONE when the `get()` callback runs once, then subsequent calls return the cached value.

## Anti-loop
- One client OR one cache pool per pass. Do not wire both unless the task needs both.
- If a custom pool already exists in `cache.yaml`, reuse it. Do not recreate `cache.app`.
