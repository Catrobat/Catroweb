<?php


namespace App\Api;

use OpenAPI\Server\Api\MediaLibraryApiInterface;
use Symfony\Component\HttpFoundation\Response;

class MediaLibraryApi implements MediaLibraryApiInterface
{
  /**
   * @inheritDoc
   */
  public function mediaPackagePackageNameGet($packageName, &$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_NOT_IMPLEMENTED;
    // TODO: Implement mediaPackagePackageNameGet() method.
  }
}