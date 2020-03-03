<?php

namespace App\Catrobat\Exceptions\Upload;

use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use App\Catrobat\StatusCode;

/**
 * Class NoGameJamException.
 */
class NoGameJamException extends InvalidCatrobatFileException
{
  /**
   * NoGameJamException constructor.
   */
  public function __construct()
  {
    parent::__construct('gamejam.nojam', StatusCode::NO_GAME_JAM);
  }
}
