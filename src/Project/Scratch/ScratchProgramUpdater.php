<?php

namespace App\Project\Scratch;

use App\Project\Event\CheckScratchProgramEvent;

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
