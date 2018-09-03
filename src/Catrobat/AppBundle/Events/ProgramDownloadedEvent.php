<?php

namespace Catrobat\AppBundle\Events;

use Catrobat\AppBundle\Entity\Program;
use Symfony\Component\EventDispatcher\Event;

class ProgramDownloadedEvent extends Event
{
  protected $program;
  protected $ip;

  public function __construct(Program $program, $ip)
  {
    $this->program = $program;
    $this->ip = $ip;
  }

  public function getProgram()
  {
    return $this->program;
  }

  public function getIp()
  {
    return $this->ip;
  }
}
