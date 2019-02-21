<?php

namespace Catrobat\AppBundle\Events;

use Catrobat\AppBundle\Entity\Program;
use Symfony\Component\EventDispatcher\Event;
use Catrobat\AppBundle\Services\ExtractedCatrobatFile;

/**
 * Class ProgramAfterInsertEvent
 * @package Catrobat\AppBundle\Events
 */
class ProgramAfterInsertEvent extends Event
{
  /**
   * @var ExtractedCatrobatFile
   */
  protected $extracted_file;
  /**
   * @var Program
   */
  protected $program;

  /**
   * ProgramAfterInsertEvent constructor.
   *
   * @param ExtractedCatrobatFile $extracted_file
   * @param Program               $program
   */
  public function __construct(ExtractedCatrobatFile $extracted_file, Program $program)
  {
    $this->extracted_file = $extracted_file;
    $this->program = $program;
  }

  /**
   * @return ExtractedCatrobatFile
   */
  public function getExtractedFile()
  {
    return $this->extracted_file;
  }

  /**
   * @return Program
   */
  public function getProgramEntity()
  {
    return $this->program;
  }
}
