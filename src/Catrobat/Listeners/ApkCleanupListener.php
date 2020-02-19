<?php

namespace App\Catrobat\Listeners;

use App\Catrobat\Events\ProgramBeforePersistEvent;
use App\Catrobat\Services\ApkRepository;
use App\Entity\Program;

/**
 * Class ApkCleanupListener.
 */
class ApkCleanupListener
{
  /**
   * @var ApkRepository
   */
  protected $repository;

  /**
   * ApkCleanupListener constructor.
   */
  public function __construct(ApkRepository $repository)
  {
    $this->repository = $repository;
  }

  public function handleEvent(ProgramBeforePersistEvent $event)
  {
    $program = $event->getProgramEntity();
    if (0 !== $program->getId())
    {
      $this->repository->remove($program->getId());
      $program->setApkStatus(Program::APK_NONE);
    }
  }
}
