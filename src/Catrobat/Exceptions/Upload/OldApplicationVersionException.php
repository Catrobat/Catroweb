<?php

namespace App\Catrobat\Exceptions\Upload;

use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use App\Catrobat\StatusCode;

/**
 * Class OldApplicationVersionException
 * @package App\Catrobat\Exceptions\Upload
 */
class OldApplicationVersionException extends InvalidCatrobatFileException
{
  /**
   * OldApplicationVersionException constructor.
   *
   * @param $debug
   */
  public function __construct($debug)
  {
    parent::__construct("errors.programversion.tooold", StatusCode::OLD_APPLICATION_VERSION, $debug);
  }
}
