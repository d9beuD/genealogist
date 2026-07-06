# Controllers (Symfony 8.1)

A controller is a PHP method that reads the `Request` and returns a `Response`. Extend `AbstractController` to get helper methods. Keep controllers thin.

## Basic controller

```php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LuckyController extends AbstractController
{
    #[Route('/lucky/number/{max}', name: 'app_lucky_number')]
    public function number(int $max): Response
    {
        $number = random_int(0, $max);

        return new Response('Lucky number: '.$number);
    }
}
```

## Rendering a template

```php
return $this->render('lucky/number.html.twig', ['number' => $number]);
```

## JSON response

```php
return $this->json(['number' => $number]);
```

## Redirecting

```php
return $this->redirectToRoute('app_lucky_number', ['max' => 10]);
return $this->redirectToRoute('homepage', [], 301); // permanent
return $this->redirect('https://symfony.com'); // external (do NOT use user input)
```

## Generating URLs

```php
$url = $this->generateUrl('app_lucky_number', ['max' => 10]);
```

## Injecting services (constructor injection preferred)

Type-hint the service; Symfony autowires it. Prefer constructor injection; action-argument injection also works.

```php
use Psr\Log\LoggerInterface;

class LuckyController extends AbstractController
{
    public function __construct(private LoggerInterface $logger) {}

    #[Route('/lucky/number/{max}')]
    public function number(int $max): Response
    {
        $this->logger->info('We are logging!');
        // ...
    }
}
```

List autowirable services: `php bin/console debug:autowiring`.

## Reading the request

```php
use Symfony\Component\HttpFoundation\Request;

public function index(Request $request): Response
{
    $page = $request->query->get('page', 1); // query string
    // $request->request for POST body, $request->files for uploads
}
```

Map a query parameter directly:

```php
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;

public function dashboard(#[MapQueryParameter] int $age): Response { /* ... */ }
```

## 404 and errors

```php
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

if (!$product) {
    throw $this->createNotFoundException('The product does not exist');
}
```

Any exception extending `HttpException` sets the matching status; others give 500.

## Automatically fetching entities (EntityValueResolver)

If a route has `{id}`, type-hint the entity to auto-query by primary key (404 if not found):

```php
use App\Entity\Product;

#[Route('/product/{id}')]
public function show(Product $product): Response { /* ... */ }
```

Fetch by another property with the `{param:argument}` syntax:

```php
#[Route('/product/{slug:product}')] // findOneBy(['slug' => $slug])
public function show(Product $product): Response { /* ... */ }
```

Or explicitly with `#[MapEntity]`:

```php
use Symfony\Bridge\Doctrine\Attribute\MapEntity;

#[Route('/product/{slug}')]
public function show(#[MapEntity(mapping: ['slug' => 'slug'])] Product $product): Response { /* ... */ }
```

Make the argument nullable (`?Product $product`) to handle "not found" yourself instead of an automatic 404.

## Generators

```bash
php bin/console make:controller BrandNewController
php bin/console make:crud Product   # full CRUD from an entity
```
