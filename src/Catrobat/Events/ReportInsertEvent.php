<?php

namespace App\Catrobat\Events;

use Symfony\Component\EventDispatcher\Event;
use App\Entity\ProgramInappropriateReport;

/**
 * Class ReportInsertEvent
 * @package App\Catrobat\Events
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
   * @return \App\Entity\ProgramInappropriateReport
   */
  public function getReport()
  {
    return $this->program;
  }
}
