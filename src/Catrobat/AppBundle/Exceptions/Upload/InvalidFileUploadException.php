<?php

namespace Catrobat\AppBundle\Exceptions\Upload;

use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\StatusCode;

/**
 * Class InvalidFileUploadException
 * @package Catrobat\AppBundle\Exceptions\Upload
 */
class InvalidFileUploadException extends InvalidCatrobatFileException
{
  /**
   * InvalidFileUploadException constructor.
   */
  public function __construct()
  {
    parent::__construct('error.upload.invalid_file_upload', StatusCode::INVALID_FILE_UPLOAD);
  }
}
