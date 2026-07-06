# Doctrine ORM (Symfony 8.1)

Entities are PHP classes mapped to database tables via `#[ORM\...]` attributes.

## Create an entity (preferred: generator)

```bash
php bin/console make:entity   # interactive; creates/updates src/Entity/<Name>.php
```

Generated entity shape:

```php
// src/Entity/Product.php
namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $price = null;

    public function getId(): ?int { return $this->id; }
    public function getName(): ?string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }
    // ... other getters/setters
}
```

## Migrations (apply schema changes)

```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

Do not hand-edit the database. Re-run only after entity changes.

## Persisting objects

Inject `EntityManagerInterface`, then `persist()` + `flush()`:

```php
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/product', name: 'create_product')]
public function create(EntityManagerInterface $entityManager): Response
{
    $product = new Product();
    $product->setName('Keyboard');
    $product->setPrice(1999);

    $entityManager->persist($product); // schedules; no query yet
    $entityManager->flush();           // runs INSERT/UPDATE

    return new Response('Saved id '.$product->getId());
}
```

Doctrine decides INSERT vs UPDATE automatically. Same workflow for updates (fetch, change, `flush()`).

## Fetching with repositories

Inject the repository (autowired) or use `EntityValueResolver` (see controllers.md).

```php
use App\Repository\ProductRepository;

#[Route('/product/{id}', name: 'product_show')]
public function show(ProductRepository $productRepository, int $id): Response
{
    $product = $productRepository->find($id);
    if (!$product) {
        throw $this->createNotFoundException('No product for id '.$id);
    }
    return $this->render('product/show.html.twig', ['product' => $product]);
}
```

Built-in repository methods:

```php
$repo->find($id);                              // by primary key
$repo->findOneBy(['name' => 'Keyboard']);      // single by criteria
$repo->findBy(['name' => 'Keyboard'], ['price' => 'ASC']); // multiple, ordered
$repo->findAll();
```

## Custom queries (in the repository)

```php
// src/Repository/ProductRepository.php
public function findAllGreaterThanPrice(int $price): array
{
    return $this->createQueryBuilder('p')
        ->andWhere('p.price > :price')
        ->setParameter('price', $price)
        ->orderBy('p.price', 'ASC')
        ->getQuery()
        ->getResult();
}
```

## Associations

Use `#[ORM\ManyToOne]`, `#[ORM\OneToMany]`, etc. Generate them with `make:entity` (answer `relation` as the field type). Example:

```php
#[ORM\ManyToOne(inversedBy: 'products')]
#[ORM\JoinColumn(nullable: false)]
private ?Category $category = null;
```

The owning side holds the foreign key (`ManyToOne` / `JoinColumn`); the inverse side uses `mappedBy`.

## CRUD generation

```bash
php bin/console make:crud Product
# creates Controller, Form/ProductType, and templates/product/*.html.twig
```

If these files already exist, edit them — do not regenerate.

## Validation from Doctrine metadata

With `auto_mapping` enabled, Symfony infers some constraints (NotNull, Length, etc.) from mapping. This does not replace explicit validation — still add constraints for user input (see forms-validation.md).
