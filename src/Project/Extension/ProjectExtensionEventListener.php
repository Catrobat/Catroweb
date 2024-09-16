<?php

declare(strict_types=1);

namespace App\Project\Extension;

use App\Project\Event\ProjectBeforePersistEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: ProjectBeforePersistEvent::class, method: 'addExtension')]
class ProjectExtensionEventListener
{
  public function __construct(
    protected ProjectExtensionManager $extension_manager,
  ) {
  }

  public function addExtension(ProjectBeforePersistEvent $event): void
  {
    $this->extension_manager->addExtensions($event->getExtractedFile(), $event->getProjectEntity());
  }
}
