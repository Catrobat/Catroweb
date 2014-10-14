<?php

namespace AppBundle\Listeners;

use AppBundle\Model\ExtractedCatrobatFile;
use AppBundle\Exceptions\InvalidCatrobatFileException;
use AppBundle\Events\ProgramBeforeInsertEvent;
use AppBundle\Services\RudeWordFilter;
use AppBundle\StatusCode;

class NameValidator
{
  private $rudeWordFilter;

  public function __construct(RudeWordFilter $rudeWordFilter)
  {
    $this->rudeWordFilter = $rudeWordFilter;
  }
  
  public function onProgramBeforeInsert(ProgramBeforeInsertEvent $event)
  {
    $this->validate($event->getExtractedFile());
  }
  
  public function validate(ExtractedCatrobatFile $file)
  {
    if ($file->getName() == null || $file->getName() == "")
    {
      throw new InvalidCatrobatFileException("program name missing");
    }
    else if (strlen($file->getName()) > 200)
    {
      throw new InvalidCatrobatFileException("program name too long");
    }

    if ($this->rudeWordFilter->containsRudeWord($file->getName()))
    {
      throw new InvalidCatrobatFileException("rude word in name", StatusCode::RUDE_WORD_IN_PROGRAM_NAME);
    }
  }

}
