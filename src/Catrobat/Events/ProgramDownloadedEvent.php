<?php

namespace App\Catrobat\Events;

use App\Entity\Program;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class ProgramDownloadedEvent.
 */
class ProgramDownloadedEvent extends Event
{
  /**
   * @var Program
   */
  protected $program;
  /**
   * @var
   */
  protected $ip;

  /**
   * ProgramDownloadedEvent constructor.
   *
   * @param $ip
   */
  public function __construct(Program $program, $ip)
  {
    $this->program = $program;
    $this->ip = $ip;
  }

  /**
   * @return Program
   */
  public function getProgram()
  {
    return $this->program;
  }

  /**
   * @return mixed
   */
  public function getIp()
  {
    return $this->ip;
  }
}
