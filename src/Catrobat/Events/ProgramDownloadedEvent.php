<?php

namespace App\Catrobat\Events;

use App\Entity\Program;
use Symfony\Contracts\EventDispatcher\Event;

class ProgramDownloadedEvent extends Event
{
  protected Program $program;

  protected ?string $ip;

  public function __construct(Program $program, ?string $ip)
  {
    $this->program = $program;
    $this->ip = $ip;
  }

  public function getProgram(): Program
  {
    return $this->program;
  }

  public function getIp(): ?string
  {
    return $this->ip;
  }
}
