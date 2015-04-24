<?php

namespace Catrobat\AppBundle\Listeners;

use Catrobat\AppBundle\Services\ExtractedCatrobatFile;
use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\Events\ProgramBeforeInsertEvent;
use Catrobat\AppBundle\Services\RudeWordFilter;
use Catrobat\AppBundle\StatusCode;

class DescriptionValidator
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
    if (strlen($file->getDescription()) > 1000 )
    {
      throw new InvalidCatrobatFileException("program description too long");
    }

    if ($this->rudeWordFilter->containsRudeWord($file->getDescription()))
    {
      throw new InvalidCatrobatFileException("rude word in descritption", StatusCode::RUDE_WORD_IN_DESCRIPTION);
    }

  }

}