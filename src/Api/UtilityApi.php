<?php

namespace App\Api;

use OpenAPI\Server\Api\UtilityApiInterface;
use Symfony\Component\HttpFoundation\Response;

class UtilityApi implements UtilityApiInterface
{
  /**
   * {@inheritdoc}
   */
  public function healthGet(&$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_NO_CONTENT;

    return null;
  }
}
