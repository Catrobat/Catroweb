<?php

namespace Catrobat\CatrowebBundle\Services\Validators;

use Catrobat\CatrowebBundle\Model\ExtractedCatrobatFile;
use Catrobat\CatrowebBundle\Exceptions\InvalidCatrobatFileException;

class NameValidator implements ExtractedCatrobatFileValidatorInterface
{
  
  /*
   * (non-PHPdoc) @see \Catrobat\CatrowebBundle\Services\Validators\ExtractedCatrobatFileValidatorInterface::validate()
   */
  public function validate(ExtractedCatrobatFile $file)
  {
    if ($file->getName() == null || $file->getName() == "")
    {
      throw new InvalidCatrobatFileException("project name missing");
    }
  }

}
