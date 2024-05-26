<?php

declare(strict_types=1);

namespace App\Api\Exceptions;

class ApiVersionNotSupportedException extends ApiException
{
  /**
   * ApiVersionNotSupportedException constructor.
   *
   * @param string          $requested_api_version the requested API version which is not supported
   * @param \Exception|null $previous              the previous exception
   */
  public function __construct(string $requested_api_version, ?\Exception $previous = null)
  {
    parent::__construct(sprintf('The requested API version \'%s\' is not supported!', $requested_api_version), 1, $previous);
  }
}
