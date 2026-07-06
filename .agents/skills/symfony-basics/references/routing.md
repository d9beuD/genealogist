# Routing (Symfony 8.1)

Routes map a URL to a controller action. Prefer attributes so route and controller live together.

## Basic route as attribute

```php
// src/Controller/BlogController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BlogController extends AbstractController
{
    #[Route('/blog', name: 'blog_list')]
    public function list(): Response
    {
        // ...
    }
}
```

Each route `name` must be unique; it is used to generate URLs.

## Route parameters

Variable parts are wrapped in `{ }`. The name becomes a controller argument.

```php
#[Route('/blog/{slug}', name: 'blog_show')]
public function show(string $slug): Response
{
    // /blog/yay-routing -> $slug = 'yay-routing'
}
```

## Validating parameters (requirements)

```php
#[Route('/blog/{page}', name: 'blog_list', requirements: ['page' => '\d+'])]
public function list(int $page): Response { /* ... */ }
```

## Matching HTTP methods

By default a route matches any verb. Restrict with `methods`:

```php
#[Route('/api/posts/{id}', methods: ['GET', 'HEAD'])]
public function show(int $id): Response { /* ... */ }

#[Route('/api/posts/{id}', methods: ['PUT'])]
public function edit(int $id): Response { /* ... */ }
```

## Generating URLs

In a controller (extends `AbstractController`):

```php
$url = $this->generateUrl('blog_show', ['slug' => 'my-post']);
```

In Twig, use `path()` (see templates.md):

```twig
<a href="{{ path('blog_show', {slug: 'my-post'}) }}">Read</a>
```

## Defining routes in YAML (optional alternative)

```yaml
# config/routes.yaml
blog_list:
    path: /blog
    controller: App\Controller\BlogController::list
    methods: GET|HEAD
```

## Debugging routes

```bash
php bin/console debug:router            # list all routes
php bin/console debug:router blog_show  # details of one route
php bin/console router:match /blog/foo  # which route matches a URL
```

Notes:
- The route attribute always wins over YAML/PHP route files.
- If multiple PHP classes share one file, only the first class's routes load.
