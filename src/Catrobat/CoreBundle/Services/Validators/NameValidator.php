<?php

namespace Catrobat\CoreBundle\Services\Validators;

use Catrobat\CoreBundle\Model\ExtractedCatrobatFile;
use Catrobat\CoreBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\CoreBundle\Events\ProjectBeforeInsertEvent;

class NameValidator
{
  
  public function onProjectBeforeInsert(ProjectBeforeInsertEvent $event)
  {
    $this->validate($event->getExtractedFile());
  }
  
  public function validate(ExtractedCatrobatFile $file)
  {
    if ($file->getName() == null || $file->getName() == "")
    {
      throw new InvalidCatrobatFileException("project name missing");
    }
    else if (strlen($file->getName()) > 200)
    {
      throw new InvalidCatrobatFileException("project name too long");
    }
  }

}
