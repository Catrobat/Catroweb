<?php

namespace Catrobat\AppBundle\Exceptions\Upload;

use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\StatusCode;

/**
 * Class InvalidXmlException
 * @package Catrobat\AppBundle\Exceptions\Upload
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
