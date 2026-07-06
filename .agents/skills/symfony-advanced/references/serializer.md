# Serializer

Symfony 8.1. Converts objects ↔ JSON/XML/CSV/YAML. Internally: normalizers (object→array) + encoders (array→format).

## Inject the service
Type-hint `SerializerInterface`.
```php
use Symfony\Component\Serializer\SerializerInterface;

public function index(SerializerInterface $serializer): Response
{
    $jsonContent = $serializer->serialize($person, 'json');
    // ...
}
```

## Serialize
```php
$jsonContent = $serializer->serialize($person, 'json');
// formats: 'json', 'xml', 'csv', 'yaml'
```

## Deserialize
```php
public function create(Request $request, SerializerInterface $serializer): Response
{
    $jsonData = $request->getContent();
    $person = $serializer->deserialize($jsonData, Person::class, 'json');
    // ...
}
```

## Building manually (outside the framework)
```php
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

$serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
$json = $serializer->serialize($person, 'json');
```

## Groups
Limit which properties are (de)serialized.
```php
use Symfony\Component\Serializer\Attribute\Groups;

class Person
{
    #[Groups(['public'])]
    public string $name;

    #[Groups(['internal'])]
    public string $secret;
}
```
```php
$serializer->serialize($person, 'json', ['groups' => 'public']);
```

## Renaming & ignoring properties
```php
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Serializer\Attribute\Ignore;

class Person
{
    #[SerializedName('full_name')]
    public string $name;

    #[Ignore]
    public string $internalId;
}
```

## Context
Pass context at call time or per-property with `#[Context]`.
```php
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

$serializer->serialize($person, 'json', [
    AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
]);
```
Per-property date format:
```php
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

class Event
{
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    public \DateTimeInterface $date;
}
```
Context builders (typed alternative to raw arrays):
```php
use Symfony\Component\Serializer\Context\Normalizer\DateTimeNormalizerContextBuilder;

$contextBuilder = (new DateTimeNormalizerContextBuilder())->withFormat('Y-m-d');
$serializer->serialize($something, 'json', $contextBuilder->toArray());
```

## Name converters
Map camelCase PHP properties to snake_case JSON keys with `CamelCaseToSnakeCaseNameConverter` passed to `ObjectNormalizer`.

## Anti-loop
- One serialize/deserialize call. Stop when output matches the expected shape.
- Prefer attributes (`#[Groups]`, `#[SerializedName]`, `#[Ignore]`, `#[Context]`) over manual normalizer wiring when running inside Symfony.
