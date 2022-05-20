<?php

namespace App\Project\Event;

use App\DB\Entity\Project\Program;
use App\Project\CatrobatFile\ExtractedCatrobatFile;
use Symfony\Contracts\EventDispatcher\Event;

class ProgramAfterInsertEvent extends Event
{
  public function __construct(protected ExtractedCatrobatFile $extracted_file, protected Program $program)
  {
  }

  public function getExtractedFile(): ExtractedCatrobatFile
  {
    return $this->extracted_file;
  }

  public function getProgramEntity(): Program
  {
    return $this->program;
  }
}
