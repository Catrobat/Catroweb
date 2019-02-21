<?php

namespace Catrobat\AppBundle\Events;

use Symfony\Component\EventDispatcher\Event;
use Catrobat\AppBundle\Entity\ProgramInappropriateReport;

/**
 * Class ReportInsertEvent
 * @package Catrobat\AppBundle\Events
 */
class ReportInsertEvent extends Event
{
  /**
   * @var
   */
  protected $category;
  /**
   * @var
   */
  protected $note;
  /**
   * @var ProgramInappropriateReport
   */
  protected $program;

  /**
   * ReportInsertEvent constructor.
   *
   * @param                            $category
   * @param                            $description
   * @param ProgramInappropriateReport $program
   */
  public function __construct($category, $description, ProgramInappropriateReport $program)
  {
    $this->category = $category;
    $this->note = $description;
    $this->program = $program;
  }

  /**
   * @return String
   */
  public function getCategory()
  {
    return $this->category;
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
