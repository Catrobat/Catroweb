<?php

namespace Catrobat\AppBundle\Exceptions\Upload;

use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\StatusCode;

class InvalidXmlException extends InvalidCatrobatFileException
{
  public function __construct($debug = "")
  {
    parent::__construct("errors.xml.invalid", StatusCode::INVALID_XML, $debug);
  }
}
