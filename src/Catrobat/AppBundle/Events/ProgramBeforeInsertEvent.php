<?php

namespace Catrobat\AppBundle\Events;

use Symfony\Component\EventDispatcher\Event;
use Catrobat\AppBundle\Services\ExtractedCatrobatFile;

/**
 * Class ProgramBeforeInsertEvent
 * @package Catrobat\AppBundle\Events
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
