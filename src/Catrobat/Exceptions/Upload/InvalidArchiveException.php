<?php

namespace App\Catrobat\Exceptions\Upload;

use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use App\Catrobat\StatusCode;

/**
 * Class InvalidArchiveException.
 */
class InvalidArchiveException extends InvalidCatrobatFileException
{
  /**
   * InvalidArchiveException constructor.
   */
  public function __construct()
  {
    parent::__construct('errors.file.invalid', StatusCode::INVALID_FILE);
  }
}
