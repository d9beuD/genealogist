# Forms & Validation (Symfony 8.1)

## Form classes (preferred over inline builders)

Put form definitions in dedicated `AbstractType` classes so they are reusable and keep controllers thin.

```php
// src/Form/Type/TaskType.php
namespace App\Form\Type;

use App\Entity\Task;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('task', TextType::class)
            ->add('dueDate', DateType::class)
            ->add('save', SubmitType::class, ['label' => 'Create Task']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Task::class]);
    }
}
```

Generate with `php bin/console make:form`.

## Processing a form (single action for render + submit)

```php
use App\Entity\Task;
use App\Form\Type\TaskType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

public function new(Request $request, EntityManagerInterface $em): Response
{
    $task = new Task();
    $form = $this->createForm(TaskType::class, $task);

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        $task = $form->getData();
        $em->persist($task);
        $em->flush();

        return $this->redirectToRoute('task_success');
    }

    return $this->render('task/new.html.twig', ['form' => $form]);
}
```

Notes:
- `handleRequest()` writes submitted data into the bound object.
- Passing `$form` (not `$form->createView()`) to `render()` sets HTTP 422 on invalid submit automatically.
- Always redirect after a successful submit (Post/Redirect/Get).

## Rendering the form (Twig)

```twig
{{ form_start(form) }}
    {{ form_widget(form) }}
{{ form_end(form) }}
```

Or render the whole form: `{{ form(form) }}`. Render fields individually with `{{ form_row(form.task) }}`.

## Validation constraints

Add `#[Assert\...]` attributes on entity properties.

```php
// src/Entity/Author.php
namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class Author
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 3)]
    private string $name;

    #[Assert\Email]
    private string $email;
}
```

Forms validate the bound object automatically on submit. To validate manually:

```php
use Symfony\Component\Validator\Validator\ValidatorInterface;

$errors = $validator->validate($author);
if (count($errors) > 0) {
    return new Response((string) $errors, 400);
}
```

## Validation groups

Tag constraints with groups, then validate only some groups.

```php
#[Assert\NotBlank(groups: ['registration'])]
#[Assert\Length(min: 10, groups: ['registration'])]
private string $password;
```

In a form type, restrict validated groups:

```php
$resolver->setDefaults([
    'data_class' => User::class,
    'validation_groups' => ['registration'],
]);
```

## Custom constraint

Two classes: the constraint (holds options/message) and its validator (logic).

```php
// src/Validator/ContainsAlphanumeric.php
namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ContainsAlphanumeric extends Constraint
{
    public string $message = 'The string "{{ string }}" contains an illegal character.';
    public string $mode = 'strict';

    public function __construct(?string $mode = null, ?string $message = null, ?array $groups = null, $payload = null)
    {
        $this->mode = $mode ?? $this->mode;
        $this->message = $message ?? $this->message;
        parent::__construct(null, $groups, $payload);
    }
}
```

```php
// src/Validator/ContainsAlphanumericValidator.php
namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ContainsAlphanumericValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ContainsAlphanumeric) {
            throw new UnexpectedTypeException($constraint, ContainsAlphanumeric::class);
        }
        if (null === $value || '' === $value) {
            return; // let NotBlank/NotNull handle empties
        }
        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }
        if (!preg_match('/^[a-zA-Z0-9]+$/', $value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}
```

By convention the validator class is the constraint name + `Validator`. Use the constraint like any other: `#[ContainsAlphanumeric]` on a property.
