<?php
namespace Catrobat\CatrowebBundle\Services\Validators;

use Catrobat\CatrowebBundle\Model\ExtractedCatrobatFile;

interface ExtractedCatrobatFileValidatorInterface
{
  public function validate(ExtractedCatrobatFile $file);
}