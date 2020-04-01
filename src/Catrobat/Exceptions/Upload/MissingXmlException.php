<?php

namespace App\Catrobat\Exceptions\Upload;

use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use App\Catrobat\StatusCode;

class MissingXmlException extends InvalidCatrobatFileException
{
  public function __construct()
  {
    parent::__construct('errors.xml.missing', StatusCode::PROJECT_XML_MISSING);
  }
}
