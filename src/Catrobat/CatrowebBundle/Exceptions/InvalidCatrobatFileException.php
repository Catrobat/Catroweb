<?php

namespace Catrobat\CatrowebBundle\Exceptions;

class InvalidCatrobatFileException extends \RuntimeException
{
  /*
   * (non-PHPdoc) @see RuntimeException::__construct()
  */
  public function __construct($message)
  {
    parent::__construct($message);
  }
}