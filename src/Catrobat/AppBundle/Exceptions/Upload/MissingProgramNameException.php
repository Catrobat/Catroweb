<?php

namespace Catrobat\AppBundle\Exceptions\Upload;

use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\StatusCode;

class MissingProgramNameException extends InvalidCatrobatFileException
{
  public function __construct()
  {
    parent::__construct("errors.name.missing", StatusCode::MISSING_PROGRAM_TITLE);
  }
}
