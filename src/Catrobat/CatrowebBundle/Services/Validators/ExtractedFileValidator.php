<?php
namespace Catrobat\CatrowebBundle\Services\Validators;

use Catrobat\CatrowebBundle\Model\ExtractedCatrobatFile;

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
