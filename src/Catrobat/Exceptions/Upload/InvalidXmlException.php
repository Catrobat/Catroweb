<?php

namespace App\Catrobat\Exceptions\Upload;

use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use App\Catrobat\StatusCode;

/**
 * Class InvalidXmlException
 * @package App\Catrobat\Exceptions\Upload
 */
class InvalidXmlException extends InvalidCatrobatFileException
{
  /**
   * InvalidXmlException constructor.
   *
   * @param string $debug
   */
  public function __construct($debug = "")
  {
    parent::__construct("errors.xml.invalid", StatusCode::INVALID_XML, $debug);
  }
}
