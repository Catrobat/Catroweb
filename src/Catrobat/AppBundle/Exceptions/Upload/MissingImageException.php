<?php

namespace Catrobat\AppBundle\Exceptions\Upload;

use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\StatusCode;

class MissingImageException extends InvalidCatrobatFileException
{
  public function __construct($debug_message)
  {
    parent::__construct("errors.image.missing", StatusCode::IMAGE_MISSING, $debug_message);
  }
}
