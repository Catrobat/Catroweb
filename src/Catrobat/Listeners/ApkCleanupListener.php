<?php

namespace App\Catrobat\Listeners;

use App\Catrobat\Events\ProgramBeforePersistEvent;
use App\Entity\Program;
use App\Repository\ApkRepository;

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
