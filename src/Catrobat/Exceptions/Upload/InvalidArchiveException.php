<?php

namespace App\Catrobat\Exceptions\Upload;

use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use App\Catrobat\StatusCode;

class InvalidArchiveException extends InvalidCatrobatFileException
{
  public function __construct()
  {
    parent::__construct('errors.file.invalid', StatusCode::INVALID_FILE);
  }
}
