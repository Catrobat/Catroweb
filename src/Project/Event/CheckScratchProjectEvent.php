<?php

declare(strict_types=1);

namespace App\Project\Event;

use Symfony\Contracts\EventDispatcher\Event;

class CheckScratchProjectEvent extends Event
{
  public function __construct(protected int $scratch_id)
  {
  }

  public function getScratchId(): int
  {
    return $this->scratch_id;
  }
}
