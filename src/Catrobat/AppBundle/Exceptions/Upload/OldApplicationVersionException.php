<?php

namespace Catrobat\AppBundle\Exceptions\Upload;

use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\StatusCode;

class OldApplicationVersionException extends InvalidCatrobatFileException
{
  public function __construct($debug)
  {
    parent::__construct("errors.programversion.tooold", StatusCode::OLD_APPLICATION_VERSION, $debug);
  }
}
