<?php

namespace Catrobat\AppBundle\Exceptions\Upload;

use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\StatusCode;

/**
 * Class UnexpectedFileException
 * @package Catrobat\AppBundle\Exceptions\Upload
 */
class UnexpectedFileException extends InvalidCatrobatFileException
{
  /**
   * UnexpectedFileException constructor.
   *
   * @param $debug
   */
  public function __construct($debug)
  {
    parent::__construct("errors.file.unexpected", StatusCode::UNEXPECTED_FILE, $debug);
  }
}
