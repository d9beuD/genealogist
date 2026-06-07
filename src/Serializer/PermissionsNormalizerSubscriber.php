<?php

declare(strict_types=1);

namespace App\Serializer;

use ApiPlatform\Serializer\SerializerContextFilter;
use App\Service\ResourcePermissions;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class PermissionsNormalizerSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ResourcePermissions $resourcePermissions,
        private NormalizerInterface $normalizer,
    ) {}

    /**
     * @return array<string, array<int, string|int>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['injectPermissions', 5],
        ];
    }

    public function injectPermissions(ViewEvent $event): void
    {
        $subject = $event->getControllerResult();

        if (!\is_object($subject)) {
            return;
        }

        $permissions = $this->resourcePermissions->getPermissions($subject);

        if ($permissions === []) {
            return;
        }

        $request = $event->getRequest();
        $format = $request->getRequestFormat();
        $context = $request->attributes->get('serialization_context', []);

        $normalized = $this->normalizer->normalize($subject, $format, $context);

        if (!\is_array($normalized)) {
            return;
        }

        $normalized['_permissions'] = $permissions;

        $event->setControllerResult($normalized);
    }
}
