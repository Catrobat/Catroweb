<?php

namespace App\Catrobat\Exceptions\Upload;

use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use App\Catrobat\StatusCode;

class RudewordInNameException extends InvalidCatrobatFileException
{
  public function __construct()
  {
    parent::__construct('errors.programname.rude', StatusCode::RUDE_WORD_IN_PROGRAM_NAME);
  }
}
