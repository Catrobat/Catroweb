<?php

namespace Catrobat\AppBundle\Exceptions\Upload;

use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\StatusCode;

/**
 * Class NoGameJamException
 * @package Catrobat\AppBundle\Exceptions\Upload
 */
class NoGameJamException extends InvalidCatrobatFileException
{
  /**
   * NoGameJamException constructor.
   */
  public function __construct()
  {
    parent::__construct("gamejam.nojam", StatusCode::NO_GAME_JAM);
  }
}
