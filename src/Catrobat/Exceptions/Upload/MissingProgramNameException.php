<?php

namespace App\Catrobat\Exceptions\Upload;

use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use App\Catrobat\StatusCode;

class MissingProgramNameException extends InvalidCatrobatFileException
{
  public function __construct()
  {
    parent::__construct('errors.name.missing', StatusCode::MISSING_PROGRAM_TITLE);
  }
}
