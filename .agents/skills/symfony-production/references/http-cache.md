# HTTP Cache (Symfony 8.1)

Pick the model that fits, set headers, stop. HTTP caching only works for safe methods (GET, HEAD). Never cache PUT/DELETE; avoid POST caching. Never mutate state on GET/HEAD.

## Enable the Symfony reverse proxy (gateway cache)
In `config/packages/framework.yaml`:
```yaml
when@prod:
    framework:
        http_cache: true
```
In debug mode Symfony adds an `X-Symfony-Cache` header. Tune with `trace_level` (`none`/`short`/`full`) and `trace_header`. For heavy traffic switch to Varnish (same HTTP rules apply).

## Model 1: Expiration caching (simplest)
Cache the whole response for a fixed time. Application is bypassed until expiry. Hard to invalidate.

Cache-Control (recommended; use both `public` and `max-age`):
```php
use Symfony\Component\HttpKernel\Attribute\Cache;

#[Cache(public: true, maxage: 3600, mustRevalidate: true)]
public function index(): Response { return $this->render('blog/index.html.twig', []); }
```
Or on the Response object:
```php
$response->setPublic();
$response->setMaxAge(3600);
```
Expires header alternative: `#[Cache(expires: '+600 seconds')]` or `$response->setExpires($date)`.
Note: `s-maxage`/`max-age` override `Expires`. Headers set in the controller take precedence over `#[Cache]`.

Conditional cache (Symfony 8.1): `#[Cache(public: true, maxage: 3600, if: "request.query.has('cache')")]` (closure or ExpressionLanguage). `#[Cache]` is repeatable; first matching condition wins.

## Model 2: Validation caching (fresh content immediately)
Cache asks the app per request whether the cached response is still valid; app returns 304 with no content if valid.

ETag:
```php
$response = $this->render('static/homepage.html.twig');
$response->setEtag(md5($response->getContent()));
$response->setPublic();
$response->isNotModified($request); // sets 304 if If-None-Match matches
```
Last-Modified / optimized (compute cheaply, return 304 before doing heavy work):
```php
$response = new Response();
$response->setEtag($article->computeETag());
$response->setLastModified($article->getPublishedAt());
$response->setPublic();
if ($response->isNotModified($request)) {
    return $response; // 304, content/headers stripped automatically
}
// ... heavy work, then render with $response
return $this->render('article/show.html.twig', ['article' => $article], $response);
```
`isNotModified()` compares `If-None-Match` vs `ETag` and `If-Modified-Since` vs `Last-Modified`.
`#[Cache(etag: "args['article'].computeETag()", lastModified: "args['article'].getUpdatedAt()", public: true)]` supports expressions and closures (Symfony 8.1: `request` and `args` vars).

## Vary (multiple representations per URI)
Default cache key is the URI. To vary by request headers:
```php
#[Cache(vary: ['Accept-Encoding', 'User-Agent'])]
```
or `$response->setVary(['Accept-Encoding', 'User-Agent']);`

## Sessions
Starting a session makes the response private/non-cacheable. To opt out:
```php
use Symfony\Component\HttpKernel\EventListener\AbstractSessionListener;
$response->headers->set(AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER, 'true');
```

## Invalidation / PURGE (avoid when possible)
Prefer short lifetimes or the validation model. If needed, override `HttpCache::invalidate()` in a `CacheKernel` that decorates `http_cache` and handles the `PURGE` method (restrict by client IP). PURGE drops all Vary variants. For complex cases use FOSHttpCacheBundle (banning, cache tagging).

## More Response methods
`$response->expire();` (mark stale), `$response->setNotModified();` (force 304), `$response->setCache([...])` for several settings at once.

## Done criteria
- Headers set via attribute or Response; reverse proxy enabled if required. Verify with `X-Symfony-Cache`. Stop.
