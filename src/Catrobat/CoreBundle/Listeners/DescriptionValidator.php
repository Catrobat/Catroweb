<?php

namespace Catrobat\CoreBundle\Listeners;

use Catrobat\CoreBundle\Model\ExtractedCatrobatFile;
use Catrobat\CoreBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\CoreBundle\Events\ProgramBeforeInsertEvent;
use Catrobat\CoreBundle\Services\RudeWordFilter;
use Catrobat\CoreBundle\StatusCode;

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

    if ($this->rudeWordFilter->containsBadWord($file->getDescription()))
    {
      throw new InvalidCatrobatFileException("rude word in descritption", StatusCode::RUDE_WORD_IN_DESCRIPTION);
    }

  }

}