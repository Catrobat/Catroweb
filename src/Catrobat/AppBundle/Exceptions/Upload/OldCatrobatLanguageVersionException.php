<?php

namespace Catrobat\AppBundle\Exceptions\Upload;

use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\StatusCode;

class OldCatrobatLanguageVersionException extends InvalidCatrobatFileException
{
  public function __construct()
  {
    parent::__construct("errors.languageversion.tooold", StatusCode::OLD_CATROBAT_LANGUAGE);
  }
}
