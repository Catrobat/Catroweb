<?php

namespace Catrobat\AppBundle\Listeners;

use Catrobat\AppBundle\Services\ApkRepository;
use Catrobat\AppBundle\Events\ProgramBeforePersistEvent;
use Catrobat\AppBundle\Entity\Program;

class ApkCleanupListener
{
  protected $repository;

  public function __construct(ApkRepository $repository)
  {
    $this->repository = $repository;
  }

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
