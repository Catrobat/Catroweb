<?php

namespace App\Project\Scratch;

use App\Project\Event\CheckScratchProgramEvent;

class ScratchProgramUpdater
{
  public function __construct(protected ScratchManager $scratch_manager)
  {
  }

  public function onCheckScratchProgram(CheckScratchProgramEvent $event): void
  {
    $this->scratch_manager->createScratchProgramFromId($event->getScratchId());
  }
}
