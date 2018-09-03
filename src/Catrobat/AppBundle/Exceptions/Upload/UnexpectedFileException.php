<?php

namespace Catrobat\AppBundle\Exceptions\Upload;

use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\StatusCode;

class UnexpectedFileException extends InvalidCatrobatFileException
{
  public function __construct($debug)
  {
    parent::__construct("errors.file.unexpected", StatusCode::UNEXPECTED_FILE, $debug);
  }
}
