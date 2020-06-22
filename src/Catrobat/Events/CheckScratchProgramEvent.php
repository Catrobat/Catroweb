<?php

namespace App\Catrobat\Events;

use Symfony\Contracts\EventDispatcher\Event;

class CheckScratchProgramEvent extends Event
{
  protected int $scratch_id;

  public function __construct(int $scratch_id)
  {
    $this->scratch_id = $scratch_id;
  }

  public function getScratchId(): int
  {
    return $this->scratch_id;
  }
}
