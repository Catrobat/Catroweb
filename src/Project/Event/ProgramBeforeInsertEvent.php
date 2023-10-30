<?php

namespace App\Project\Event;

use App\Project\CatrobatFile\ExtractedCatrobatFile;
use Symfony\Contracts\EventDispatcher\Event;

class ProgramBeforeInsertEvent extends Event
{
  public function __construct(protected ExtractedCatrobatFile $extracted_file)
  {
  }

  public function getExtractedFile(): ExtractedCatrobatFile
  {
    return $this->extracted_file;
  }
}
