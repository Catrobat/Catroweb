<?php

declare(strict_types=1);

namespace App\Project\EventSubscriber;

use App\Project\Event\ProjectDownloadEvent;
use App\Project\ProjectManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProjectDownloadEventSubscriber implements EventSubscriberInterface
{
  public function __construct(protected ProjectManager $project_manager)
  {
  }

  public function onProjectDownload(ProjectDownloadEvent $event): void
  {
    $this->project_manager->increaseDownloads($event->getProject(), $event->getUser());
  }

  #[\Override]
  public static function getSubscribedEvents(): array
  {
    return [
      ProjectDownloadEvent::class => 'onProjectDownload',
    ];
  }
}
