<?php

declare(strict_types=1);

namespace App\Project\Event;

use App\DB\Entity\Project\Program;
use App\Project\CatrobatFile\ExtractedCatrobatFile;
use Symfony\Contracts\EventDispatcher\Event;

class ProjectAfterInsertEvent extends Event
{
  public function __construct(protected ExtractedCatrobatFile $extracted_file, protected Program $project)
  {
  }

  public function getExtractedFile(): ExtractedCatrobatFile
  {
    return $this->extracted_file;
  }

  public function getProjectEntity(): Program
  {
    return $this->project;
  }
}
