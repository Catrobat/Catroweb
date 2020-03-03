<?php

namespace App\Api;

use OpenAPI\Server\Api\ProjectsApiInterface;
use OpenAPI\Server\Model\Flavor;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class ProjectsApi implements ProjectsApiInterface
{
  /**
   * {@inheritdoc}
   */
  public function projectProjectIdGet($projectId, &$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_NOT_IMPLEMENTED;
    // TODO: Implement projectProjectIdGet() method.
  }

  /**
   * {@inheritdoc}
   */
  public function projectsFeaturedGet($platform = null, $maxVersion = null, $limit = 20, $offset = 0, Flavor $flavor = null, &$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_NOT_IMPLEMENTED;
    // TODO: Implement projectsFeaturedGet() method.
  }

  /**
   * {@inheritdoc}
   */
  public function projectsMostDownloadedGet($maxVersion = null, $limit = 20, $offset = 0, Flavor $flavor = null, &$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_NOT_IMPLEMENTED;
    // TODO: Implement projectsMostDownloadedGet() method.
  }

  /**
   * {@inheritdoc}
   */
  public function projectsMostViewedGet($maxVersion = null, $limit = 20, $offset = 0, Flavor $flavor = null, &$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_NOT_IMPLEMENTED;
    // TODO: Implement projectsMostViewedGet() method.
  }

  /**
   * {@inheritdoc}
   */
  public function projectsPublicUserUserIdGet($userId, $maxVersion = null, $limit = 20, $offset = null, &$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_NOT_IMPLEMENTED;
    // TODO: Implement projectsPublicUserUserIdGet() method.
  }

  /**
   * {@inheritdoc}
   */
  public function projectsRandomProgramsGet($maxVersion = null, $limit = 20, $offset = 0, Flavor $flavor = null, &$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_NOT_IMPLEMENTED;
    // TODO: Implement projectsRandomProgramsGet() method.
  }

  /**
   * {@inheritdoc}
   */
  public function projectsRecentGet($maxVersion = null, $limit = 20, $offset = 0, Flavor $flavor = null, &$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_NOT_IMPLEMENTED;
    // TODO: Implement projectsRecentGet() method.
  }

  /**
   * {@inheritdoc}
   */
  public function projectsSearchGet($maxVersion = null, $limit = 20, $offset = 0, Flavor $flavor = null, &$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_NOT_IMPLEMENTED;
    // TODO: Implement projectsSearchGet() method.
  }

  /**
   * {@inheritdoc}
   */
  public function projectsUploadPost($token, $checksum = null, UploadedFile $file = null, Flavor $flavor = null, array $tags = null, &$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_NOT_IMPLEMENTED;
    // TODO: Implement projectsUploadPost() method.
  }

  /**
   * {@inheritdoc}
   */
  public function projectsUserUserIdGet($userId, $maxVersion = null, $limit = 20, $offset = null, $token, &$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_NOT_IMPLEMENTED;
    // TODO: Implement projectsUserUserIdGet() method.
  }
}
