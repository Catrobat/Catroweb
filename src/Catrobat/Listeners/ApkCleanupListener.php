<?php

namespace App\Catrobat\Listeners;

use App\Catrobat\Services\ApkRepository;
use App\Catrobat\Events\ProgramBeforePersistEvent;
use App\Entity\Program;

/**
 * Class ApkCleanupListener
 * @package App\Catrobat\Listeners
 */
class ApkCleanupListener
{
  /**
   * @var ApkRepository
   */
  protected $repository;

  /**
   * ApkCleanupListener constructor.
   *
   * @param ApkRepository $repository
   */
  public function __construct(ApkRepository $repository)
  {
    $this->repository = $repository;
  }

  /**
   * @param ProgramBeforePersistEvent $event
   */
  public function handleEvent(ProgramBeforePersistEvent $event)
  {
    $program = $event->getProgramEntity();
    if ($program->getId() !== 0)
    {
      $this->repository->remove($program->getId());
      $program->setApkStatus(Program::APK_NONE);
    }
  }
}
