<?php

namespace App\Project\Event;

use App\DB\Entity\Project\ProgramInappropriateReport;
use Symfony\Contracts\EventDispatcher\Event;

class ReportInsertEvent extends Event
{
  public function __construct(protected ?string $category, protected ?string $note, protected ProgramInappropriateReport $project)
  {
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
    return $this->project;
  }
}
