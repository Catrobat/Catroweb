<?php

namespace App\Project\Event;

use App\DB\Entity\Project\Program;
use App\Project\CatrobatFile\ExtractedCatrobatFile;
use Symfony\Contracts\EventDispatcher\Event;

class ProgramBeforePersistEvent extends Event
{
  protected ExtractedCatrobatFile $extracted_file;

  protected Program $program;

  public function __construct(ExtractedCatrobatFile $extracted_file, Program $program)
  {
    $this->extracted_file = $extracted_file;
    $this->program = $program;
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
