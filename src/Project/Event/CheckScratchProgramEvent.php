<?php

namespace App\Project\Event;

use Symfony\Contracts\EventDispatcher\Event;

class CheckScratchProgramEvent extends Event
{
  public function __construct(protected int $scratch_id)
  {
  }

  public function getScratchId(): int
  {
    return $this->scratch_id;
  }
}
