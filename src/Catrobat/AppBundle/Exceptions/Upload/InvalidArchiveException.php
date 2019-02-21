<?php

namespace Catrobat\AppBundle\Exceptions\Upload;

use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\StatusCode;

/**
 * Class InvalidArchiveException
 * @package Catrobat\AppBundle\Exceptions\Upload
 */
class InvalidArchiveException extends InvalidCatrobatFileException
{
  /**
   * InvalidArchiveException constructor.
   */
  public function __construct()
  {
    parent::__construct("errors.file.invalid", StatusCode::INVALID_FILE);
  }
}
