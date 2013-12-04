<?php

namespace Catrobat\CoreBundle\Services\Validators;

use Catrobat\CoreBundle\Model\ExtractedCatrobatFile;
use Catrobat\CoreBundle\Exceptions\InvalidCatrobatFileException;

class DescriptionValidator implements ExtractedCatrobatFileValidatorInterface
{
  
  /*
   * (non-PHPdoc) @see \Catrobat\CoreBundle\Services\Validators\ExtractedCatrobatFileValidatorInterface::validate()
   */
  public function validate(ExtractedCatrobatFile $file)
  {
    if (strlen($file->getDescription()) > 1000 )
    {
      throw new InvalidCatrobatFileException("project description too long");
    }

  }

}