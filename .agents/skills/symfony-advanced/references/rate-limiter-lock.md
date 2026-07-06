# Rate Limiter + Lock

Two components. Pick the one the task needs.

---

# Rate Limiter (throttle requests/actions)

Symfony 8.1.

## 1. Install + configure
```
composer require symfony/rate-limiter
```
`config/packages/rate_limiter.yaml` (under `framework`):
```yaml
framework:
    rate_limiter:
        anonymous_api:
            policy: 'fixed_window'
            limit: 100
            interval: '60 minutes'
        authenticated_api:
            policy: 'token_bucket'
            limit: 5000
            rate: { interval: '15 minutes', amount: 500 }
```
Policies: `fixed_window`, `sliding_window`, `token_bucket`, `no_limit`.

## 2. Inject
Symfony creates a `RateLimiterFactoryInterface` target named after each limiter.
```php
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

public function index(
    Request $request,
    #[Target('anonymous_api')] RateLimiterFactoryInterface $rateLimiter,
): Response {
    // ...
}
```

## 3. Consume
Create a limiter keyed by a unique client identifier (IP, user, API key).
```php
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

$limiter = $rateLimiter->create($request->getClientIp());

if (false === $limiter->consume(1)->isAccepted()) {
    throw new TooManyRequestsHttpException();
}
// or: $limiter->consume(1)->ensureAccepted(); // throws RateLimitExceededException
```
Blocking wait until tokens free up:
```php
$limiter->reserve(1)->wait();
// $limiter->reserve(1, 20)->wait(); // wait at most 20 seconds
```
DONE when `consume()->isAccepted()` returns the expected bool.

---

# Lock (mutex / critical section)

Symfony 8.1. Prevents concurrent execution.

## Configure (optional store)
`config/packages/lock.yaml` under `framework:` with a `lock:` DSN (e.g. `flock`, `redis://...`, `semaphore`).

## Use
Inject `LockFactory`.
```php
use Symfony\Component\Lock\LockFactory;

public function downloadPdf(LockFactory $factory, MyPdfGeneratorService $pdf): Response
{
    $lock = $factory->createLock('pdf-creation');

    // non-blocking: returns false if already held
    if (!$lock->acquire()) {
        // could not get the lock
    }

    // blocking: waits until acquired
    $lock->acquire(true);

    try {
        // ... critical section ...
    } finally {
        $lock->release();
    }
}
```
DONE when `acquire()` returns true and `release()` is called (use try/finally).

## Anti-loop
- One limiter OR one lock per pass.
- If the limiter/lock config already exists, reuse it — do not redefine.
- Always release locks in a `finally` block. Stop once the critical section is protected.
