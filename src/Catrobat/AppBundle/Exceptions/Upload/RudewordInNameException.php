<?php

namespace Catrobat\AppBundle\Exceptions\Upload;

use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\StatusCode;

/**
 * Class RudewordInNameException
 * @package Catrobat\AppBundle\Exceptions\Upload
 */
class RudewordInNameException extends InvalidCatrobatFileException
{
  /**
   * RudewordInNameException constructor.
   */
  public function __construct()
  {
    parent::__construct("errors.programname.rude", StatusCode::RUDE_WORD_IN_PROGRAM_NAME);
  }
}
