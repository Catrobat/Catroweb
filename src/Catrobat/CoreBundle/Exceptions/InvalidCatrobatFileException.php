<?php

namespace Catrobat\CoreBundle\Exceptions;

class InvalidCatrobatFileException extends \RuntimeException
{
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