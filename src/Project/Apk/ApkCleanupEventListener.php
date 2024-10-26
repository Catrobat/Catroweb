<?php

declare(strict_types=1);

namespace App\Project\Apk;

use App\DB\Entity\Project\Program;
use App\Project\Event\ProjectBeforePersistEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: ProjectBeforePersistEvent::class, method: 'removeApk')]
class ApkCleanupEventListener
{
  public function __construct(protected ApkRepository $repository)
  {
  }

  public function removeApk(ProjectBeforePersistEvent $event): void
  {
    $project = $event->getProjectEntity();
    if (null !== $project->getId()) {
      $this->repository->remove($project->getId());
      $project->setApkStatus(Program::APK_NONE);
    }
  }
}
