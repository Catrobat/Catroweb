<?php

namespace App\Catrobat\Exceptions\Upload;

use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use App\Catrobat\StatusCode;

/**
 * Class OldCatrobatLanguageVersionException
 * @package App\Catrobat\Exceptions\Upload
 */
class OldCatrobatLanguageVersionException extends InvalidCatrobatFileException
{
  /**
   * OldCatrobatLanguageVersionException constructor.
   */
  public function __construct()
  {
    parent::__construct("errors.languageversion.tooold", StatusCode::OLD_CATROBAT_LANGUAGE);
  }
}
