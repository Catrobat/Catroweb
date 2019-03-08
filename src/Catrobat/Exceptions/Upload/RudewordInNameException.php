<?php

namespace App\Catrobat\Exceptions\Upload;

use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use App\Catrobat\StatusCode;

/**
 * Class RudewordInNameException
 * @package App\Catrobat\Exceptions\Upload
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
