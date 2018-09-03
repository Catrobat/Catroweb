<?php

namespace Catrobat\AppBundle\Exceptions\Upload;

use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\StatusCode;

class MissingChecksumException extends InvalidCatrobatFileException
{
  public function __construct()
  {
    parent::__construct("errors.checksum.missing", StatusCode::MISSING_CHECKSUM);
  }
}
