<?php

namespace Catrobat\AppBundle\Exceptions\Upload;

use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\StatusCode;

/**
 * Class MissingProgramNameException
 * @package Catrobat\AppBundle\Exceptions\Upload
 */
class MissingProgramNameException extends InvalidCatrobatFileException
{
  /**
   * MissingProgramNameException constructor.
   */
  public function __construct()
  {
    parent::__construct("errors.name.missing", StatusCode::MISSING_PROGRAM_TITLE);
  }
}
