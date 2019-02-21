<?php

namespace Catrobat\AppBundle\Exceptions\Upload;

use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\StatusCode;

/**
 * Class OldApplicationVersionException
 * @package Catrobat\AppBundle\Exceptions\Upload
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
