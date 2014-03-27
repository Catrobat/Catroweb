<?php

namespace Catrobat\CoreBundle\Exceptions;

class InvalidCatrobatFileException extends \RuntimeException
{
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