<?php

namespace App\Catrobat\Listeners;

use App\Catrobat\Events\ProgramBeforePersistEvent;
use App\Catrobat\Services\ApkRepository;
use App\Entity\Program;

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
    if (null !== $program->getId())
    {
      $this->repository->remove($program->getId());
      $program->setApkStatus(Program::APK_NONE);
    }
  }
}
