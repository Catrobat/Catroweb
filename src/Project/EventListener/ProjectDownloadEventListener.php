<?php

declare(strict_types=1);

namespace App\Project\EventListener;

use App\Project\Event\ProjectDownloadEvent;
use App\Project\ProjectStatisticsService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: ProjectDownloadEvent::class, method: 'onProjectDownload')]
class ProjectDownloadEventListener
{
  public function __construct(protected ProjectStatisticsService $project_statistics_service)
  {
  }

  public function onProjectDownload(ProjectDownloadEvent $event): void
  {
    $this->project_statistics_service->increaseDownloads($event->getProject(), $event->getUser());
  }
}
