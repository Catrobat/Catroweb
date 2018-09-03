<?php

namespace Catrobat\AppBundle\Exceptions\Upload;

use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\StatusCode;

class DescriptionTooLongException extends InvalidCatrobatFileException
{
  public function __construct()
  {
    parent::__construct("errors.description.toolong", StatusCode::DESCRIPTION_TOO_LONG);
  }
}
