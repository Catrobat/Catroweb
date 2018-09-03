<?php

namespace Catrobat\AppBundle\Exceptions\Upload;

use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\StatusCode;

class InvalidChecksumException extends InvalidCatrobatFileException
{
  public function __construct()
  {
    parent::__construct("errors.checksum.invalid", StatusCode::INVALID_CHECKSUM);
  }
}
