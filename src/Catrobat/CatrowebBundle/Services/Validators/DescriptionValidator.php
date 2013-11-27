<?php

namespace Catrobat\CatrowebBundle\Services\Validators;

use Catrobat\CatrowebBundle\Model\ExtractedCatrobatFile;
use Catrobat\CatrowebBundle\Exceptions\InvalidCatrobatFileException;

class DescriptionValidator implements ExtractedCatrobatFileValidatorInterface
{
  
  /*
   * (non-PHPdoc) @see \Catrobat\CatrowebBundle\Services\Validators\ExtractedCatrobatFileValidatorInterface::validate()
   */
  public function validate(ExtractedCatrobatFile $file)
  {
    if (strlen($file->getDescription()) > 1000 )
    {
      throw new InvalidCatrobatFileException("project description too long");
    }

  }

}