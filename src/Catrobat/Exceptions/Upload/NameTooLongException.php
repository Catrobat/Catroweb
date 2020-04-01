<?php

namespace App\Catrobat\Exceptions\Upload;

use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use App\Catrobat\StatusCode;

class NameTooLongException extends InvalidCatrobatFileException
{
  public function __construct()
  {
    parent::__construct('errors.name.toolong', StatusCode::PROGRAM_NAME_TOO_LONG);
  }
}
