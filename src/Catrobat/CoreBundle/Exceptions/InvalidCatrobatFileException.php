<?php

namespace Catrobat\CoreBundle\Exceptions;

class InvalidCatrobatFileException extends \RuntimeException
{
  const INTERNAL_SERVER_ERROR = 500;
  const MISSING_POST_DATA = 501;
  const MISSING_CHECKSUM = 503;
  const INVALID_CHECKSUM = 504;
  const PROJECT_XML_MISSING = 507;
  const IMAGE_MISSING = 524;
  
  protected $code;
  
  /*
   * (non-PHPdoc) @see RuntimeException::__construct()
  */
  public function __construct($code)
  {
    parent::__construct($code);
    $this->code = $code;
  }
  
  public function getStatusCode()
  {
    return $this->code;
  }
}