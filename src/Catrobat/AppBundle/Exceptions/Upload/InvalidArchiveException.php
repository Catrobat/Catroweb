<?php

namespace Catrobat\AppBundle\Exceptions\Upload;

use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\StatusCode;

class InvalidArchiveException extends InvalidCatrobatFileException
{
  public function __construct()
  {
    parent::__construct("errors.file.invalid", StatusCode::INVALID_FILE);
  }
}
