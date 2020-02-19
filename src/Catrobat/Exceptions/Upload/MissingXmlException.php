<?php

namespace App\Catrobat\Exceptions\Upload;

use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use App\Catrobat\StatusCode;

/**
 * Class MissingXmlException.
 */
class MissingXmlException extends InvalidCatrobatFileException
{
  /**
   * MissingXmlException constructor.
   */
  public function __construct()
  {
    parent::__construct('errors.xml.missing', StatusCode::PROJECT_XML_MISSING);
  }
}
