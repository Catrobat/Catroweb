<?php

namespace App\Catrobat\Listeners;

use App\Catrobat\Events\CheckScratchProgramEvent;
use App\Entity\ScratchManager;

class ScratchProgramUpdater
{
  protected ScratchManager $scratch_manager;

  public function __construct(ScratchManager $scratch_manager)
  {
    $this->scratch_manager = $scratch_manager;
  }

  public function onCheckScratchProgram(CheckScratchProgramEvent $event): void
  {
    $this->scratch_manager->createScratchProgramFromId($event->getScratchId());
  }
}
