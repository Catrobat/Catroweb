<?php

namespace App\Project\Event;

use App\DB\Entity\Project\ProjectInappropriateReport;
use Symfony\Contracts\EventDispatcher\Event;

class ReportInsertEvent extends Event
{
  public function __construct(protected ?string $category, protected ?string $note, protected ProjectInappropriateReport $project)
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

  public function getReport(): ProjectInappropriateReport
  {
    return $this->project;
  }
}
