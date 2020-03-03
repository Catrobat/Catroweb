<?php

namespace App\Catrobat\Exceptions\Upload;

use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use App\Catrobat\StatusCode;

/**
 * Class DescriptionTooLongException.
 */
class DescriptionTooLongException extends InvalidCatrobatFileException
{
  /**
   * DescriptionTooLongException constructor.
   */
  public function __construct()
  {
    parent::__construct('errors.description.toolong', StatusCode::DESCRIPTION_TOO_LONG);
  }
}
