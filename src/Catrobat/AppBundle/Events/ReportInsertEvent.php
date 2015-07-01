<?php

namespace Catrobat\AppBundle\Events;

use Symfony\Component\EventDispatcher\Event;
use Catrobat\AppBundle\Entity\ProgramInappropriateReport;

class ReportInsertEvent extends Event
{
    protected $note;
    protected $program;

    public function __construct($description, ProgramInappropriateReport $program)
    {
        $this->note = $description;
        $this->program = $program;
    }

  /**
   * @return String
   */
  public function getNote()
  {
      return $this->note;
  }

  /**
   * @return \Catrobat\AppBundle\Entity\ProgramInappropriateReport
   */
  public function getReport()
  {
      return $this->program;
  }
}
