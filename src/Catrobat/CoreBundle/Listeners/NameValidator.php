<?php

namespace Catrobat\CoreBundle\Listeners;

use Catrobat\CoreBundle\Model\ExtractedCatrobatFile;
use Catrobat\CoreBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\CoreBundle\Events\ProgramBeforeInsertEvent;
use Catrobat\CoreBundle\Services\RudeWordFilter;
use Catrobat\CoreBundle\StatusCode;

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

    if ($this->rudeWordFilter->containsBadWord($file->getName()))
    {
      throw new InvalidCatrobatFileException("rude word in name", StatusCode::RUDE_WORD_IN_PROGRAM_NAME);
    }
  }

}
