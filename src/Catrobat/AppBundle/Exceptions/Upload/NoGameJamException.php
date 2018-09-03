<?php

namespace Catrobat\AppBundle\Exceptions\Upload;

use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\StatusCode;

class NoGameJamException extends InvalidCatrobatFileException
{
  public function __construct()
  {
    parent::__construct("gamejam.nojam", StatusCode::NO_GAME_JAM);
  }
}
