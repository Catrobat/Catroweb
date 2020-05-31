<?php

namespace App\Catrobat\Exceptions\Upload;

use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use App\Catrobat\StatusCode;

class RudewordInDescriptionException extends InvalidCatrobatFileException
{
  public function __construct()
  {
    parent::__construct('errors.description.rude', StatusCode::RUDE_WORD_IN_DESCRIPTION);
  }
}
