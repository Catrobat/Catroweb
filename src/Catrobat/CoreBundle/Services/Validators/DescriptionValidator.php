<?php

namespace Catrobat\CoreBundle\Services\Validators;

use Catrobat\CoreBundle\Model\ExtractedCatrobatFile;
use Catrobat\CoreBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\CoreBundle\Events\ProjectBeforeInsertEvent;

class DescriptionValidator
{
  
  public function onProjectBeforeInsert(ProjectBeforeInsertEvent $event)
  {
    $this->validate($event->getExtractedFile());
  }
  
  public function validate(ExtractedCatrobatFile $file)
  {
    if (strlen($file->getDescription()) > 1000 )
    {
      throw new InvalidCatrobatFileException("project description too long");
    }

  }

}