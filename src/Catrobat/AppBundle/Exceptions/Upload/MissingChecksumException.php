<?php

namespace Catrobat\AppBundle\Exceptions\Upload;

use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\StatusCode;

/**
 * Class MissingChecksumException
 * @package Catrobat\AppBundle\Exceptions\Upload
 */
class MissingChecksumException extends InvalidCatrobatFileException
{
  /**
   * MissingChecksumException constructor.
   */
  public function __construct()
  {
    parent::__construct("errors.checksum.missing", StatusCode::MISSING_CHECKSUM);
  }
}
