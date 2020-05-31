<?php

namespace App\Catrobat\Exceptions\Upload;

use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use App\Catrobat\StatusCode;

class OldCatrobatLanguageVersionException extends InvalidCatrobatFileException
{
  public function __construct()
  {
    parent::__construct('errors.languageversion.tooold', StatusCode::OLD_CATROBAT_LANGUAGE);
  }
}
