<?php

namespace Catrobat\AppBundle\Exceptions\Upload;

use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\StatusCode;

/**
 * Class MissingImageException
 * @package Catrobat\AppBundle\Exceptions\Upload
 */
class MissingImageException extends InvalidCatrobatFileException
{
  /**
   * MissingImageException constructor.
   *
   * @param $debug_message
   */
  public function __construct($debug_message)
  {
    parent::__construct("errors.image.missing", StatusCode::IMAGE_MISSING, $debug_message);
  }
}
