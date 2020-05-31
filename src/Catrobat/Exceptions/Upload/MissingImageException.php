<?php

namespace App\Catrobat\Exceptions\Upload;

use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use App\Catrobat\StatusCode;

class MissingImageException extends InvalidCatrobatFileException
{
  public function __construct(string $debug_message)
  {
    parent::__construct('errors.image.missing', StatusCode::IMAGE_MISSING, $debug_message);
  }
}
