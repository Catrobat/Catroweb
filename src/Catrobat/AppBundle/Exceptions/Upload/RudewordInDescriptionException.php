<?php

namespace Catrobat\AppBundle\Exceptions\Upload;

use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\StatusCode;

class RudewordInDescriptionException extends InvalidCatrobatFileException
{
  public function __construct()
  {
    parent::__construct("errors.description.rude", StatusCode::RUDE_WORD_IN_DESCRIPTION);
  }
}
