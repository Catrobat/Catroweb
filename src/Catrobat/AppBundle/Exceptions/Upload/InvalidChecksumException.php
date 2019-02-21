<?php

namespace Catrobat\AppBundle\Exceptions\Upload;

use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\StatusCode;

/**
 * Class InvalidChecksumException
 * @package Catrobat\AppBundle\Exceptions\Upload
 */
class InvalidChecksumException extends InvalidCatrobatFileException
{
  /**
   * InvalidChecksumException constructor.
   */
  public function __construct()
  {
    parent::__construct("errors.checksum.invalid", StatusCode::INVALID_CHECKSUM);
  }
}
