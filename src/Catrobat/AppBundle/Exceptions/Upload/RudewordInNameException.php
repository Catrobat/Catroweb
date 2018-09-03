<?php

namespace Catrobat\AppBundle\Exceptions\Upload;

use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\StatusCode;

class RudewordInNameException extends InvalidCatrobatFileException
{
  public function __construct()
  {
    parent::__construct("errors.programname.rude", StatusCode::RUDE_WORD_IN_PROGRAM_NAME);
  }
}
