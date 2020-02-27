<?php

namespace App\Api;

use OpenAPI\Server\Api\ProjectsApiInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProjectsApi implements ProjectsApiInterface
{
  /**
   * @var string
   */
  private $token;

  /**
   * {@inheritdoc}
   */
  public function setPandaAuth($value)
  {
    $this->token = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function projectProjectIdGet(string $projectId, &$responseCode, array &$responseHeaders)
  {
    // TODO: Implement projectProjectIdGet() method.
  }

  /**
   * {@inheritdoc}
   */
  public function projectsFeaturedGet(string $platform = null, string $maxVersion = null, int $limit = 20, int $offset = 0, string $flavor = null, &$responseCode, array &$responseHeaders)
  {
    // TODO: Implement projectsFeaturedGet() method.
  }

  /**
   * {@inheritdoc}
   */
  public function projectsGet(string $projectType, string $acceptLanguage = null, string $maxVersion = null, int $limit = 20, int $offset = 0, string $flavor = null, &$responseCode, array &$responseHeaders)
  {
    // TODO: Implement projectsGet() method.
  }

  /**
   * {@inheritdoc}
   */
  public function projectsPost(string $acceptLanguage = null, string $checksum = null, UploadedFile $file = null, string $flavor = null, array $tags = null, &$responseCode, array &$responseHeaders)
  {
    // TODO: Implement projectsPost() method.
  }

  /**
   * {@inheritdoc}
   */
  public function projectsSearchGet(string $queryString, string $maxVersion = null, int $limit = 20, int $offset = 0, string $flavor = null, &$responseCode, array &$responseHeaders)
  {
    // TODO: Implement projectsSearchGet() method.
  }

  /**
   * {@inheritdoc}
   */
  public function projectsUserGet(string $maxVersion = null, int $limit = 20, int $offset = 0, string $flavor = null, &$responseCode, array &$responseHeaders)
  {
    // TODO: Implement projectsUserGet() method.
  }

  /**
   * {@inheritdoc}
   */
  public function projectsUserUserIdGet(string $userId, string $maxVersion = null, int $limit = 20, int $offset = 0, string $flavor = null, &$responseCode, array &$responseHeaders)
  {
    // TODO: Implement projectsUserUserIdGet() method.
  }
}
