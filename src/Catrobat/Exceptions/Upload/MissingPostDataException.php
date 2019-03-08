<?php

namespace App\Catrobat\Exceptions\Upload;

use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use App\Catrobat\StatusCode;

/**
 * Class MissingPostDataException
 * @package App\Catrobat\Exceptions\Upload
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
