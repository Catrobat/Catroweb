<?php

namespace Catrobat\AppBundle\Exceptions\Upload;

use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\StatusCode;

/**
 * Class OldCatrobatLanguageVersionException
 * @package Catrobat\AppBundle\Exceptions\Upload
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
