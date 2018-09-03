<?php

namespace Catrobat\AppBundle\Exceptions\Upload;

use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\StatusCode;

class MissingXmlException extends InvalidCatrobatFileException
{
  public function __construct()
  {
    parent::__construct("errors.xml.missing", StatusCode::PROJECT_XML_MISSING);
  }
}
