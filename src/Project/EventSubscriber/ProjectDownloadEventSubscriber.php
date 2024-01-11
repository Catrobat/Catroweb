<?php

namespace App\Project\EventSubscriber;

use App\Project\Event\ProjectDownloadEvent;
use App\Project\ProjectManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProjectDownloadEventSubscriber implements EventSubscriberInterface
{
  public function __construct(protected ProjectManager $program_manager)
  {
  }

  public function onProjectDownload(ProjectDownloadEvent $event): void
  {
    $this->program_manager->increaseDownloads($event->getProject(), $event->getUser());
  }

  public static function getSubscribedEvents(): array
  {
    return [
      ProjectDownloadEvent::class => 'onProjectDownload',
    ];
  }
}
