<?php

namespace App\Catrobat\Exceptions\Upload;

use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use App\Catrobat\StatusCode;

class InvalidFileUploadException extends InvalidCatrobatFileException
{
  public function __construct()
  {
    parent::__construct('error.upload.invalid_file_upload', StatusCode::INVALID_FILE_UPLOAD);
  }
}
