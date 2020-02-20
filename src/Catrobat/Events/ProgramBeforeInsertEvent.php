<?php

namespace App\Catrobat\Events;

use App\Catrobat\Services\ExtractedCatrobatFile;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class ProgramBeforeInsertEvent
 * @package App\Catrobat\Events
 */
class ProgramBeforeInsertEvent extends Event
{
  /**
   * @var ExtractedCatrobatFile
   */
  protected $extracted_file;

  /**
   * ProgramBeforeInsertEvent constructor.
   *
   * @param ExtractedCatrobatFile $extracted_file
   */
  public function __construct(ExtractedCatrobatFile $extracted_file)
  {
    $this->extracted_file = $extracted_file;
  }

  /**
   * @return ExtractedCatrobatFile
   */
  public function getExtractedFile()
  {
    return $this->extracted_file;
  }
}
