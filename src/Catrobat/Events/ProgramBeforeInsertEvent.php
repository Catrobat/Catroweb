<?php

namespace App\Catrobat\Events;

use App\Catrobat\Services\ExtractedCatrobatFile;
use Symfony\Contracts\EventDispatcher\Event;

class ProgramBeforeInsertEvent extends Event
{
  protected ExtractedCatrobatFile $extracted_file;

  public function __construct(ExtractedCatrobatFile $extracted_file)
  {
    $this->extracted_file = $extracted_file;
  }

  public function getExtractedFile(): ExtractedCatrobatFile
  {
    return $this->extracted_file;
  }
}
