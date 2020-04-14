<?php

namespace App\Api\Exceptions;

use Exception;

/**
 * Class ApiVersionNotSupportedException.
 */
class APIVersionNotSupportedException extends Exception
{
  /**
   * ApiVersionNotSupportedException constructor.
   *
   * @param string         $requested_api_version the requested API version which is not supported
   * @param Exception|null $previous              the previous exception
   */
  public function __construct($requested_api_version, Exception $previous = null)
  {
    parent::__construct("The requested API version {$requested_api_version} is not supported!", 1, $previous);
  }
}
