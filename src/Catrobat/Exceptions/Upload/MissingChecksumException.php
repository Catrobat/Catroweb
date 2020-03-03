<?php

namespace App\Catrobat\Exceptions\Upload;

use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use App\Catrobat\StatusCode;

/**
 * Class MissingChecksumException.
 */
class MissingChecksumException extends InvalidCatrobatFileException
{
  /**
   * MissingChecksumException constructor.
   */
  public function __construct()
  {
    parent::__construct('errors.checksum.missing', StatusCode::MISSING_CHECKSUM);
  }
}
