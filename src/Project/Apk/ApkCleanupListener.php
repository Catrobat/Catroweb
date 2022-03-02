<?php

namespace App\Project\Apk;

use App\DB\Entity\Project\Program;
use App\Project\Event\ProgramBeforePersistEvent;

class ApkCleanupListener
{
  protected ApkRepository $repository;

  public function __construct(ApkRepository $repository)
  {
    $this->repository = $repository;
  }

  public function handleEvent(ProgramBeforePersistEvent $event): void
  {
    $program = $event->getProgramEntity();
    if (null !== $program->getId()) {
      $this->repository->remove($program->getId());
      $program->setApkStatus(Program::APK_NONE);
    }
  }
}
