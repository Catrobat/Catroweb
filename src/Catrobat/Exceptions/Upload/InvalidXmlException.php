<?php

namespace App\Catrobat\Exceptions\Upload;

use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use App\Catrobat\StatusCode;

class InvalidXmlException extends InvalidCatrobatFileException
{
  public function __construct(string $debug = '')
  {
    parent::__construct('errors.xml.invalid', StatusCode::INVALID_XML, $debug);
  }
}
