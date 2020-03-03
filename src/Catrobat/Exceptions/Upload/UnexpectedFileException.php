<?php

namespace App\Catrobat\Exceptions\Upload;

use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use App\Catrobat\StatusCode;

/**
 * Class UnexpectedFileException.
 */
class UnexpectedFileException extends InvalidCatrobatFileException
{
  /**
   * UnexpectedFileException constructor.
   *
   * @param $debug
   */
  public function __construct($debug)
  {
    parent::__construct('errors.file.unexpected', StatusCode::UNEXPECTED_FILE, $debug);
  }
}
