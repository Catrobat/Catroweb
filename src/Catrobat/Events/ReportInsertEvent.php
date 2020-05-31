<?php

namespace App\Catrobat\Events;

use App\Entity\ProgramInappropriateReport;
use Symfony\Contracts\EventDispatcher\Event;

class ReportInsertEvent extends Event
{
  protected ?string $category;

  protected ?string $note;

  protected ProgramInappropriateReport $program;

  public function __construct(?string $category, ?string $description, ProgramInappropriateReport $program)
  {
    $this->category = $category;
    $this->note = $description;
    $this->program = $program;
  }

  public function getCategory(): ?string
  {
    return $this->category;
  }

  public function getNote(): ?string
  {
    return $this->note;
  }

  public function getReport(): ProgramInappropriateReport
  {
    return $this->program;
  }
}
