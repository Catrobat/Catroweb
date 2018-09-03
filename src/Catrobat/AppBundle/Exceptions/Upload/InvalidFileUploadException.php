<?php

namespace Catrobat\AppBundle\Exceptions\Upload;

use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\StatusCode;

class InvalidFileUploadException extends InvalidCatrobatFileException
{
  public function __construct()
  {
    parent::__construct('error.upload.invalid_file_upload', StatusCode::INVALID_FILE_UPLOAD);
  }
}
