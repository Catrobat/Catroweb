<?php

namespace App\Catrobat\Exceptions\Upload;

use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use App\Catrobat\StatusCode;

class UnexpectedFileException extends InvalidCatrobatFileException
{
  public function __construct(string $debug_message)
  {
    parent::__construct('errors.file.unexpected', StatusCode::UNEXPECTED_FILE, $debug_message);
  }
}
