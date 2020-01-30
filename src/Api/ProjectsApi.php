<?php

namespace App\Api;

use OpenAPI\Server\Api\ProjectsApiInterface;
use OpenAPI\Server\Model\Flavor;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;


class ProjectsApi implements ProjectsApiInterface
{

  /**
   * @inheritDoc
   */
  public function projectProjectIdGet($projectId, &$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_NOT_IMPLEMENTED;
    // TODO: Implement projectProjectIdGet() method.
  }

  /**
   * @inheritDoc
   */
  public function projectsFeaturedGet($platform = null, $maxVersion = null, $limit = 20, $offset = 0, Flavor $flavor = null, &$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_NOT_IMPLEMENTED;
    // TODO: Implement projectsFeaturedGet() method.
  }

  /**
   * @inheritDoc
   */
  public function projectsMostDownloadedGet($maxVersion = null, $limit = 20, $offset = 0, Flavor $flavor = null, &$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_NOT_IMPLEMENTED;
    // TODO: Implement projectsMostDownloadedGet() method.
  }

  /**
   * @inheritDoc
   */
  public function projectsMostViewedGet($maxVersion = null, $limit = 20, $offset = 0, Flavor $flavor = null, &$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_NOT_IMPLEMENTED;
    // TODO: Implement projectsMostViewedGet() method.
  }

  /**
   * @inheritDoc
   */
  public function projectsPublicUserUserIdGet($userId, $maxVersion = null, $limit = 20, $offset = null, &$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_NOT_IMPLEMENTED;
    // TODO: Implement projectsPublicUserUserIdGet() method.
  }

  /**
   * @inheritDoc
   */
  public function projectsRandomProgramsGet($maxVersion = null, $limit = 20, $offset = 0, Flavor $flavor = null, &$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_NOT_IMPLEMENTED;
    // TODO: Implement projectsRandomProgramsGet() method.
  }

  /**
   * @inheritDoc
   */
  public function projectsRecentGet($maxVersion = null, $limit = 20, $offset = 0, Flavor $flavor = null, &$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_NOT_IMPLEMENTED;
    // TODO: Implement projectsRecentGet() method.
  }

  /**
   * @inheritDoc
   */
  public function projectsSearchGet($maxVersion = null, $limit = 20, $offset = 0, Flavor $flavor = null, &$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_NOT_IMPLEMENTED;
    // TODO: Implement projectsSearchGet() method.
  }

  /**
   * @inheritDoc
   */
  public function projectsUploadPost($token, $checksum = null, UploadedFile $file = null, Flavor $flavor = null, array $tags = null, &$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_NOT_IMPLEMENTED;
    // TODO: Implement projectsUploadPost() method.
  }

  /**
   * @inheritDoc
   */
  public function projectsUserUserIdGet($userId, $maxVersion = null, $limit = 20, $offset = null, $token, &$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_NOT_IMPLEMENTED;
    // TODO: Implement projectsUserUserIdGet() method.
  }
}