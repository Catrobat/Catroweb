<?php

namespace App\Catrobat\Exceptions\Upload;

use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use App\Catrobat\StatusCode;

/**
 * Class MissingImageException
 * @package App\Catrobat\Exceptions\Upload
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
