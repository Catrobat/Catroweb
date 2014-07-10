<?php

namespace Catrobat\CoreBundle\Listeners;

use Catrobat\CoreBundle\Model\ExtractedCatrobatFile;
use Catrobat\CoreBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\CoreBundle\Events\ProgramBeforeInsertEvent;

class XmlFileValidator
{
  
  public function onProgramBeforeInsert(ProgramBeforeInsertEvent $event)
  {
    $this->validate($event->getExtractedFile());
  }
  
  public function validate(ExtractedCatrobatFile $file)
  {

  }

}