<?php

namespace App\Catrobat\Exceptions\Upload;

use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use App\Catrobat\StatusCode;

/**
 * Class MissingProgramNameException
 * @package App\Catrobat\Exceptions\Upload
 */
class MissingProgramNameException extends InvalidCatrobatFileException
{
  /**
   * MissingProgramNameException constructor.
   */
  public function __construct()
  {
    parent::__construct("errors.name.missing", StatusCode::MISSING_PROGRAM_TITLE);
  }
}
