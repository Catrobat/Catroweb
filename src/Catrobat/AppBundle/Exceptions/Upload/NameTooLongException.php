<?php

namespace Catrobat\AppBundle\Exceptions\Upload;

use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\StatusCode;

class NameTooLongException extends InvalidCatrobatFileException
{
  public function __construct()
  {
    parent::__construct("errors.name.toolong", StatusCode::PROGRAM_NAME_TOO_LONG);
  }
}
