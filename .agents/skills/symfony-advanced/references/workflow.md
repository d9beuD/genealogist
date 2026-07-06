# Workflow & State Machine

Symfony 8.1. A definition = places + transitions. `type: workflow` (multiple active places) or `type: state_machine` (single place).

## 1. Install
```
composer require symfony/workflow
```

## 2. Configure
`config/packages/workflow.yaml`:
```yaml
framework:
    workflows:
        blog_publishing:
            type: 'workflow' # or 'state_machine'
            audit_trail:
                enabled: true
            marking_store:
                type: 'method'
                property: 'currentPlace'
            supports:
                - App\Entity\BlogPost
            initial_marking: draft
            places:          # optional; inferred from transitions if omitted
                - draft
                - reviewed
                - rejected
                - published
            transitions:
                to_review:
                    from: draft
                    to:   reviewed
                publish:
                    from: reviewed
                    to:   published
                reject:
                    from: reviewed
                    to:   rejected
```
The `property` must exist on the supported entity (here `BlogPost::$currentPlace`). For a `workflow` it stores multiple places; for a `state_machine` a single place.

Debug: `php bin/console workflow:dump blog_publishing` and `config:dump-reference framework workflows`.

## 3. Inject the workflow
One service is created per workflow. Use `#[Target('<workflow_name>')]`.
```php
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Workflow\WorkflowInterface;

public function __construct(
    #[Target('blog_publishing')] private WorkflowInterface $workflow,
) {
}
```

## 4. Use it
```php
$workflow->can($post, 'publish');            // bool
$workflow->can($post, 'to_review');          // bool

if ($workflow->can($post, 'to_review')) {
    $workflow->apply($post, 'to_review');    // performs the transition
}

$transitions = $workflow->getEnabledTransitions($post);
$transition  = $workflow->getEnabledTransition($post, 'publish');
```
`apply()` returns a `Marking`; `$marking->getContext()` exposes context passed to apply.

DONE when `apply()`/`can()` reflect the configured transitions.

## 5. Transition listeners (attributes)
Behave like `#[AsEventListener]`. Available attributes:
`AsAnnounceListener`, `AsCompletedListener`, `AsEnterListener`, `AsEnteredListener`, `AsGuardListener`, `AsLeaveListener`, `AsTransitionListener`.
```php
use Symfony\Component\Workflow\Attribute\AsTransitionListener;
use Symfony\Component\Workflow\Event\TransitionEvent;

class ArticleWorkflowEventListener
{
    #[AsTransitionListener(workflow: 'blog_publishing', transition: 'publish')]
    public function onPublish(TransitionEvent $event): void
    {
        // ...
    }
}
```

## Guard events
Use a guard listener to block a transition conditionally (call `$event->setBlocked(true)` / `addTransitionBlocker`). Then `can()` returns false for that subject.

## Anti-loop
- If `workflow.yaml` already defines the workflow, reuse it — do not redefine places/transitions.
- One transition per `apply()`. Check `can()` first. Stop once the marking changes as expected.
