<?php

namespace App\Catrobat\Events;

use App\Entity\ProgramInappropriateReport;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class ReportInsertEvent.
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
   * @param $category
   * @param $description
   */
  public function __construct($category, $description, ProgramInappropriateReport $program)
  {
    $this->category = $category;
    $this->note = $description;
    $this->program = $program;
  }

  /**
   * @return string
   */
  public function getCategory()
  {
    return $this->category;
  }

  /**
   * @return string
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
