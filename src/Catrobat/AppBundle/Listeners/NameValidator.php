<?php

namespace Catrobat\AppBundle\Listeners;

use Catrobat\AppBundle\Services\ExtractedCatrobatFile;
use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\Events\ProgramBeforeInsertEvent;
use Catrobat\AppBundle\Services\RudeWordFilter;
use Catrobat\AppBundle\StatusCode;
use Catrobat\AppBundle\Exceptions\Upload\MissingProgramNameException;
use Catrobat\AppBundle\Exceptions\Upload\NameTooLongException;
use Catrobat\AppBundle\Exceptions\Upload\RudewordInNameException;

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
    if ($file->getName() == null || $file->getName() == '')
    {
      throw new MissingProgramNameException();
    }
    elseif (strlen($file->getName()) > 200)
    {
      throw new NameTooLongException();
    }

    if ($this->rudeWordFilter->containsRudeWord($file->getName()))
    {
      throw new RudewordInNameException();
    }
  }
}
