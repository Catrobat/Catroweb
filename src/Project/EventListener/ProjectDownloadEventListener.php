<?php

declare(strict_types=1);

namespace App\Project\EventListener;

use App\Project\Event\ProjectDownloadEvent;
use App\Project\ProjectManager;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: ProjectDownloadEvent::class, method: 'onProjectDownload')]
class ProjectDownloadEventListener
{
  public function __construct(protected ProjectManager $project_manager)
  {
  }

  public function onProjectDownload(ProjectDownloadEvent $event): void
  {
    $this->project_manager->increaseDownloads($event->getProject(), $event->getUser());
  }
}
