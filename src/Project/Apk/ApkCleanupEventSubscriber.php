<?php

namespace App\Project\Apk;

use App\DB\Entity\Project\Project;
use App\Project\Event\ProjectBeforePersistEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ApkCleanupEventSubscriber implements EventSubscriberInterface
{
  public function __construct(protected ApkRepository $repository)
  {
  }

  public function handleEvent(ProjectBeforePersistEvent $event): void
  {
    $project = $event->getProjectEntity();
    if (null !== $project->getId()) {
      $this->repository->remove($project->getId());
      $project->setApkStatus(Project::APK_NONE);
    }
  }

  public static function getSubscribedEvents(): array
  {
    return [ProjectBeforePersistEvent::class => 'handleEvent'];
  }
}
