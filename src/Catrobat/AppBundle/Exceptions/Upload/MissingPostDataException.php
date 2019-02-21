<?php

namespace Catrobat\AppBundle\Exceptions\Upload;

use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\StatusCode;

/**
 * Class MissingPostDataException
 * @package Catrobat\AppBundle\Exceptions\Upload
 */
class MissingPostDataException extends InvalidCatrobatFileException
{
  /**
   * MissingPostDataException constructor.
   */
  public function __construct()
  {
    parent::__construct("errors.post-data", StatusCode::MISSING_POST_DATA);
  }
}
