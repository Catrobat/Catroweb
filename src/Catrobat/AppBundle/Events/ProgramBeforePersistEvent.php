<?php

namespace Catrobat\AppBundle\Events;

use Catrobat\AppBundle\Entity\Program;
use Symfony\Component\EventDispatcher\Event;
use Catrobat\AppBundle\Services\ExtractedCatrobatFile;

class ProgramBeforePersistEvent extends Event
{
  protected $extracted_file;
  protected $program;

  public function __construct(ExtractedCatrobatFile $extracted_file, Program $program)
  {
    $this->extracted_file = $extracted_file;
    $this->program = $program;
  }

  public function getExtractedFile()
  {
    return $this->extracted_file;
  }

  public function getProgramEntity()
  {
    return $this->program;
  }
}
