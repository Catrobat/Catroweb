<?php

namespace App\Project\Extension;

use App\Project\Event\ProgramBeforePersistEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProjectExtensionEventSubscriber implements EventSubscriberInterface
{
  public function __construct(
    protected ProjectExtensionManager $extension_manager,
  ) {
  }

  public function onEvent(ProgramBeforePersistEvent $event): void
  {
    $this->extension_manager->addExtensions($event->getExtractedFile(), $event->getProgramEntity());
  }

  public static function getSubscribedEvents(): array
  {
    return [ProgramBeforePersistEvent::class => 'onEvent'];
  }
}
