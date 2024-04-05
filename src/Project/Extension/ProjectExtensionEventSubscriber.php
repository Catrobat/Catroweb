<?php

declare(strict_types=1);

namespace App\Project\Extension;

use App\Project\Event\ProjectBeforePersistEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProjectExtensionEventSubscriber implements EventSubscriberInterface
{
  public function __construct(
    protected ProjectExtensionManager $extension_manager,
  ) {
  }

  public function onEvent(ProjectBeforePersistEvent $event): void
  {
    $this->extension_manager->addExtensions($event->getExtractedFile(), $event->getProjectEntity());
  }

  public static function getSubscribedEvents(): array
  {
    return [ProjectBeforePersistEvent::class => 'onEvent'];
  }
}
