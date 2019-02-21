<?php

namespace Catrobat\AppBundle\Exceptions\Upload;

use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\StatusCode;

/**
 * Class NameTooLongException
 * @package Catrobat\AppBundle\Exceptions\Upload
 */
class NameTooLongException extends InvalidCatrobatFileException
{
  /**
   * NameTooLongException constructor.
   */
  public function __construct()
  {
    parent::__construct("errors.name.toolong", StatusCode::PROGRAM_NAME_TOO_LONG);
  }
}
