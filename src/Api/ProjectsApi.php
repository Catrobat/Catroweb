<?php

namespace App\Api;

use App\Api\Services\Base\AbstractApiController;
use App\Api\Services\Projects\ProjectsApiFacade;
use App\Catrobat\Requests\AddProgramRequest;
use Exception;
use OpenAPI\Server\Api\ProjectsApiInterface;
use OpenAPI\Server\Model\ProjectReportRequest;
use OpenAPI\Server\Model\UploadErrorResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

final class ProjectsApi extends AbstractApiController implements ProjectsApiInterface
{
  private ProjectsApiFacade $facade;

  public function __construct(ProjectsApiFacade $facade)
  {
    $this->facade = $facade;
  }

  /**
   * {@inheritdoc}
   *
   * @throws Exception
   */
  public function projectIdGet(string $id, &$responseCode, array &$responseHeaders)
  {
    $project = $this->facade->getLoader()->findProjectByID($id);
    if (is_null($project)) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createProjectDataResponse($project);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function projectsFeaturedGet(string $platform = null, string $max_version = null, ?int $limit = 20, ?int $offset = 0, string $flavor = null, &$responseCode = null, array &$responseHeaders = null): array
  {
    $max_version = $this->getDefaultMaxVersionOnNull($max_version);
    $limit = $this->getDefaultLimitOnNull($limit);
    $offset = $this->getDefaultOffsetOnNull($offset);
    $flavor = $this->getDefaultFlavorOnNull($flavor);
    $platform = $this->getDefaultPlatformOnNull($platform);

    $featured_projects = $this->facade->getLoader()->getFeaturedProjects($flavor, $limit, $offset, $platform, $max_version);

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createFeaturedProjectsResponse($featured_projects);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  /**
   * {@inheritdoc}
   *
   * @throws Exception
   */
  public function projectsGet(string $category, ?string $accept_language = null, ?string $max_version = null, ?int $limit = 20, ?int $offset = 0, ?string $flavor = null, &$responseCode = null, array &$responseHeaders = null): array
  {
    $max_version = $this->getDefaultMaxVersionOnNull($max_version);
    $limit = $this->getDefaultLimitOnNull($limit);
    $offset = $this->getDefaultOffsetOnNull($offset);
    $accept_language = $this->getDefaultAcceptLanguageOnNull($accept_language);
    $flavor = $this->getDefaultFlavorOnNull($flavor);

    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    $projects = $this->facade->getLoader()->getProjectsFromCategory($category, $max_version, $limit, $offset, $flavor, $user);

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createProjectsDataResponse($projects);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function projectIdRecommendationsGet(string $id, string $category, ?string $accept_language = null, string $max_version = null, ?int $limit = 20, ?int $offset = 0, string $flavor = null, &$responseCode = null, array &$responseHeaders = null)
  {
    $max_version = $this->getDefaultMaxVersionOnNull($max_version);
    $limit = $this->getDefaultLimitOnNull($limit);
    $offset = $this->getDefaultOffsetOnNull($offset);
    $accept_language = $this->getDefaultAcceptLanguageOnNull($accept_language);
    $flavor = $this->getDefaultFlavorOnNull($flavor);

    $project = $this->facade->getLoader()->findProjectByID($id, true);
    if (is_null($project)) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $recommended_projects = $this->facade->getLoader()->getRecommendedProjects(
      $id, $category, $max_version, $limit, $offset, $flavor, $this->facade->getAuthenticationManager()->getAuthenticatedUser()
    );

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createProjectsDataResponse($recommended_projects);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function projectsPost(string $checksum, UploadedFile $file, ?string $accept_language = null, ?string $flavor = null, ?bool $private = false, &$responseCode = null, array &$responseHeaders = null)
  {
    $accept_language = $this->getDefaultAcceptLanguageOnNull($accept_language);
    $flavor = $this->getDefaultFlavorOnNull($flavor);
    $private = $private ?? false;

    $validation_wrapper = $this->facade->getRequestValidator()->validateUploadFile($checksum, $file, $accept_language);
    if ($validation_wrapper->hasError()) {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;
      $error_response = new UploadErrorResponse($validation_wrapper->getErrors());
      $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $error_response);
      $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

      return $error_response;
    }

    // Getting the user who uploaded
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();

    // Needed (for tests) to make sure everything is up to date (followers, ..)
    $this->facade->getProcessor()->refreshUser($user);

    try {
      $project = $this->facade->getProcessor()->addProject(
        new AddProgramRequest(
          $user, $file, $this->facade->getLoader()->getClientIp(), $accept_language, $flavor
        )
      );
    } catch (Exception $e) {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;
      $error_response = $this->facade->getResponseManager()->createUploadErrorResponse($accept_language);
      $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $error_response);
      $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

      return $error_response;
    }

    // Setting the program's attributes
    $project->setPrivate($private);
    $this->facade->getProcessor()->saveProject($project);

    // Since we have come this far, the project upload is completed
    $responseCode = Response::HTTP_CREATED;
    $responseHeaders['Location'] = $this->facade->getResponseManager()->createProjectLocation($project);

    return null;
  }

  /**
   * {@inheritdoc}
   *
   * @throws Exception
   */
  public function projectsSearchGet(string $query, ?string $max_version = null, ?int $limit = 20, ?int $offset = 0, ?string $flavor = null, &$responseCode = null, array &$responseHeaders = null)
  {
    $max_version = $this->getDefaultMaxVersionOnNull($max_version);
    $limit = $this->getDefaultLimitOnNull($limit);
    $offset = $this->getDefaultOffsetOnNull($offset);
    $flavor = $this->getDefaultFlavorOnNull($flavor);

    $programs = $this->facade->getLoader()->searchProjects($query, $limit, $offset, $max_version, $flavor);

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createProjectsDataResponse($programs);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  /**
   * {@inheritdoc}
   *
   * @throws Exception
   */
  public function projectsCategoriesGet(?string $max_version = null, ?string $flavor = null, ?string $accept_language = null, &$responseCode = null, array &$responseHeaders = null): array
  {
    $max_version = $this->getDefaultMaxVersionOnNull($max_version);
    $accept_language = $this->getDefaultAcceptLanguageOnNull($accept_language);
    $limit = $this->getDefaultLimitOnNull(null);
    $offset = $this->getDefaultOffsetOnNull(null);
    $flavor = $this->getDefaultFlavorOnNull($flavor);

    $response = [];

    //removed recommended + random on purpose
    $categories = ['recent', 'random', 'most_viewed', 'most_downloaded', 'example', 'scratch'];
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();

    foreach ($categories as $category) {
      $projects = $this->facade->getLoader()->getProjectsFromCategory($category, $max_version, $limit, $offset, $flavor, $user);
      $response[] = $this->facade->getResponseManager()->createProjectCategoryResponse($projects, $category, $accept_language);
    }

    $responseCode = Response::HTTP_OK;
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  /**
   * {@inheritdoc}
   *
   * @throws Exception
   */
  public function projectsUserGet(?string $max_version = null, ?int $limit = 20, ?int $offset = 0, ?string $flavor = null, &$responseCode = null, array &$responseHeaders = null)
  {
    $max_version = $this->getDefaultMaxVersionOnNull($max_version);
    $limit = $this->getDefaultLimitOnNull($limit);
    $offset = $this->getDefaultOffsetOnNull($offset);
    $flavor = $this->getDefaultFlavorOnNull($flavor);

    $user = $this->facade->getAuthenticationManager()->getUserFromAuthenticationToken($this->getAuthenticationToken());
    if (is_null($user)) {
      $responseCode = Response::HTTP_FORBIDDEN;

      return null;
    }

    $user_projects = $this->facade->getLoader()->getUserProjects($user->getId(), $limit, $offset, $flavor, $max_version);

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createProjectsDataResponse($user_projects);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  /**
   * {@inheritdoc}
   *
   * @throws Exception
   */
  public function projectsUserIdGet(string $id, ?string $max_version = null, ?int $limit = 20, ?int $offset = 0, ?string $flavor = null, &$responseCode = null, array &$responseHeaders = null)
  {
    $max_version = $this->getDefaultMaxVersionOnNull($max_version);
    $limit = $this->getDefaultLimitOnNull($limit);
    $offset = $this->getDefaultOffsetOnNull($offset);
    $flavor = $this->getDefaultFlavorOnNull($flavor);

    if (!$this->facade->getRequestValidator()->validateUserExists($id)) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $projects = $this->facade->getLoader()->getUserPublicPrograms($id, $limit, $offset, $flavor, $max_version);

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createProjectsDataResponse($projects);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function projectIdReportPost(string $id, ProjectReportRequest $project_report_request, &$responseCode, array &$responseHeaders)
  {
    // TODO: Implement projectIdReportPost() method.

    $responseCode = Response::HTTP_NOT_IMPLEMENTED;

    return null;
  }

  public function projectIdDelete(string $id, &$responseCode, array &$responseHeaders)
  {
    // TODO: Implement projectIdDelete() method.
    $responseCode = Response::HTTP_NOT_IMPLEMENTED;

    return null;
  }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function projectsStealProject(string $user_id, string $programId, &$responseCode = null)
    {
        if (!$this->facade->getRequestValidator()->validateUserExists($user_id)) {
            $responseCode = Response::HTTP_NOT_FOUND;

            return null;
        }

        $user = $this->facade->getAuthenticationManager()->getUserFromAuthenticationToken($this->getAuthenticationToken());
        if (is_null($user)) {
            $responseCode = Response::HTTP_FORBIDDEN;

            return null;
        }

        $program = $this->facade->getLoader()->findProjectByID($programId, true);
        if (is_null($program)) {
            $responseCode = Response::HTTP_NOT_FOUND;

            return null;
        }

        $program->setUser($user);

        $this->facade->getProcessor()->saveProject($program);

        $responseCode = Response::HTTP_OK;
    }
}
