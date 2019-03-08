<?php

namespace App\Catrobat\Events;

use App\Entity\Program;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class ProgramDownloadedEvent
 * @package App\Catrobat\Events
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
   * @param Program $program
   * @param         $ip
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
