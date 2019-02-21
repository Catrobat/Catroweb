<?php

namespace Catrobat\AppBundle\Exceptions\Upload;

use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\StatusCode;

/**
 * Class MissingXmlException
 * @package Catrobat\AppBundle\Exceptions\Upload
 */
class MissingXmlException extends InvalidCatrobatFileException
{
  /**
   * MissingXmlException constructor.
   */
  public function __construct()
  {
    parent::__construct("errors.xml.missing", StatusCode::PROJECT_XML_MISSING);
  }
}
