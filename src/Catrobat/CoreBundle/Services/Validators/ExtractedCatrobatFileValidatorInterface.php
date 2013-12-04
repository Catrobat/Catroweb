<?php
namespace Catrobat\CoreBundle\Services\Validators;

use Catrobat\CoreBundle\Model\ExtractedCatrobatFile;

interface ExtractedCatrobatFileValidatorInterface
{
  public function validate(ExtractedCatrobatFile $file);
}