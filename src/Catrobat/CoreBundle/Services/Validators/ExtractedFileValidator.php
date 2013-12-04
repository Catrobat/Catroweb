<?php
namespace Catrobat\CoreBundle\Services\Validators;

use Catrobat\CoreBundle\Model\ExtractedCatrobatFile;

class ExtractedFileValidator implements ExtractedCatrobatFileValidatorInterface
{
  private $validators = array();
  
  public function validate(ExtractedCatrobatFile $file)
  {
    foreach ($this->validators as $validator)
    {
      $validator->validate($file);
    }
  }
  
  public function addValidator(ExtractedCatrobatFileValidatorInterface $validator)
  {
    $this->validators[] = $validator;
  }
}
