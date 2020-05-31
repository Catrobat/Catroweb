<?php

namespace App\Catrobat\Exceptions\Upload;

use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use App\Catrobat\StatusCode;

class MissingPostDataException extends InvalidCatrobatFileException
{
  public function __construct()
  {
    parent::__construct('errors.post-data', StatusCode::MISSING_POST_DATA);
  }
}
