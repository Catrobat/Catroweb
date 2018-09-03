<?php

namespace Catrobat\AppBundle\Exceptions\Upload;

use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\StatusCode;

class MissingPostDataException extends InvalidCatrobatFileException
{
  public function __construct()
  {
    parent::__construct("errors.post-data", StatusCode::MISSING_POST_DATA);
  }
}
