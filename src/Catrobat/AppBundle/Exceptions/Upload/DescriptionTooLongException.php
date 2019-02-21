<?php

namespace Catrobat\AppBundle\Exceptions\Upload;

use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\StatusCode;

/**
 * Class DescriptionTooLongException
 * @package Catrobat\AppBundle\Exceptions\Upload
 */
class DescriptionTooLongException extends InvalidCatrobatFileException
{
  /**
   * DescriptionTooLongException constructor.
   */
  public function __construct()
  {
    parent::__construct("errors.description.toolong", StatusCode::DESCRIPTION_TOO_LONG);
  }
}
