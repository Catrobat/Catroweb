<?php

declare(strict_types=1);

namespace App\Api;

use App\Api\Services\Base\AbstractApiController;
use App\Api\Services\Projects\ProjectsApiFacade;
use App\Api\Services\Reactions\ReactionsApiFacade;
use App\Api\Services\Reactions\ReactionsApiProcessor;
use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\ProgramDownloads;
use App\Project\AddProjectRequest;
use App\Project\Event\ProjectDownloadEvent;
use OpenAPI\Server\Api\ProjectsApiInterface;
use OpenAPI\Server\Model\ProjectReportRequest;
use OpenAPI\Server\Model\ProjectResponse;
use OpenAPI\Server\Model\ReactionRequest;
use OpenAPI\Server\Model\UpdateProjectErrorResponse;
use OpenAPI\Server\Model\UpdateProjectFailureResponse;
use OpenAPI\Server\Model\UpdateProjectRequest;
use OpenAPI\Server\Model\UploadErrorResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class ProjectsApi extends AbstractApiController implements ProjectsApiInterface
{
  public function __construct(
    private readonly ProjectsApiFacade $facade,
    private readonly ReactionsApiFacade $reactions_facade,
  ) {
  }

  #[\Override]
  public function projectIdGet(string $id, int &$responseCode, array &$responseHeaders): ?ProjectResponse
  {
    $project = $this->facade->getLoader()->findProjectByID($id, true);
    if (is_null($project)) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createProjectDataResponse($project, 'ALL');
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  #[\Override]
  public function projectIdPut(string $id, UpdateProjectRequest $update_project_request, string $accept_language, int &$responseCode, array &$responseHeaders): UpdateProjectErrorResponse|UpdateProjectFailureResponse|null
  {
    $project = $this->facade->getLoader()->findProjectByID($id, true);
    if (is_null($project)) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    if (is_null($user)) {
      $responseCode = Response::HTTP_UNAUTHORIZED;

      return null;
    }

    if (!is_null($project->getUser()) && $project->getUser() !== $user) {
      // project needs to be owned by the logged-in user
      $responseCode = Response::HTTP_FORBIDDEN;

      return null;
    }

    $validation_wrapper = $this->facade->getRequestValidator()->validateUpdateRequest($update_project_request, $accept_language);

    if ($validation_wrapper->hasError()) {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;
      $error_response = new UpdateProjectErrorResponse($validation_wrapper->getErrors());
      $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $error_response);
      $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

      return $error_response;
    }

    $result = $this->facade->getProcessor()->updateProject($project, $update_project_request);
    if (true === $result) {
      $responseCode = Response::HTTP_NO_CONTENT;

      return null;
    }

    $responseCode = Response::HTTP_INTERNAL_SERVER_ERROR;
    $error_response = $this->facade->getResponseManager()->createUpdateFailureResponse($result, $accept_language);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $error_response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $error_response;
  }

  #[\Override]
  public function projectsFeaturedGet(string $platform, string $max_version, int $limit, int $offset, string $attributes, string $flavor, int &$responseCode, array &$responseHeaders): array
  {
    $featured_projects = $this->facade->getLoader()->getFeaturedProjects($flavor, $limit, $offset, $platform, $max_version);

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createFeaturedProjectsResponse($featured_projects, $attributes);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  /**
   * @throws \JsonException
   * @throws \Psr\Cache\InvalidArgumentException
   */
  #[\Override]
  public function projectsGet(string $category, string $accept_language, string $max_version, int $limit, int $offset, string $attributes, string $flavor, int &$responseCode, array &$responseHeaders): array
  {
    $locale = $this->facade->getResponseManager()->sanitizeLocale($accept_language);
    $cache_id = sprintf('projectsGet_%s_%s_%s_%s_%d_%d', $category, $locale, $flavor, $max_version, $limit, $offset);

    // Don't cache 'recent' category as it changes frequently
    if ('recent' === $category) {
      $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
      $projects = $this->facade->getLoader()->getProjectsFromCategory($category, $max_version, $limit, $offset, $flavor, $user);

      $responseCode = Response::HTTP_OK;
      $response = $this->facade->getResponseManager()->createProjectsDataResponse($projects, $attributes);
      $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
      $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

      return $response;
    }

    $cached = $this->facade->getResponseManager()->getCachedResponse($cache_id);
    if (null !== $cached) {
      $responseCode = $cached['response_code'];
      $responseHeaders = $cached['response_headers'];

      return $cached['response'];
    }

    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    $projects = $this->facade->getLoader()->getProjectsFromCategory($category, $max_version, $limit, $offset, $flavor, $user);

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createProjectsDataResponse($projects, $attributes);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);
    $this->facade->getResponseManager()->cacheResponse($cache_id, $responseCode, $responseHeaders, $response);

    return $response;
  }

  #[\Override]
  public function projectIdRecommendationsGet(string $id, string $category, string $accept_language, string $max_version, int $limit, int $offset, string $attributes, string $flavor, int &$responseCode, array &$responseHeaders): ?array
  {
    $project = $this->facade->getLoader()->findProjectByID($id, true);
    if (is_null($project)) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $recommended_projects = $this->facade->getLoader()->getRecommendedProjects(
      $id, $category, $max_version, $limit, $offset, $flavor, $this->facade->getAuthenticationManager()->getAuthenticatedUser()
    );

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createProjectsDataResponse($recommended_projects, $attributes);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  #[\Override]
  public function projectsPost(string $checksum, UploadedFile $file, string $accept_language, string $flavor, bool $private, int &$responseCode, array &$responseHeaders): array|object|null
  {
    // Getting the user who uploaded
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();

    if (!$user->isVerified() && $this->facade->getLoader()->forceUserVerification()) {
      $responseCode = Response::HTTP_FORBIDDEN;

      return null;
    }

    $validation_wrapper = $this->facade->getRequestValidator()->validateUploadFile($checksum, $file, $accept_language);
    if ($validation_wrapper->hasError()) {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;
      $error_response = new UploadErrorResponse($validation_wrapper->getErrors());
      $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $error_response);
      $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

      return $error_response;
    }

    // Needed (for tests) to make sure everything is up to date (followers, ..)
    $this->facade->getProcessor()->refreshUser($user);

    try {
      $project = $this->facade->getProcessor()->addProject(
        new AddProjectRequest(
          $user, $file, $this->facade->getLoader()->getClientIp(), $accept_language, $flavor
        )
      );
    } catch (\Exception $e) {
      $this->facade->getLogger()->critical('Project Upload broken: '.$e->getMessage().$e->getTraceAsString());
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;
      $error_response = $this->facade->getResponseManager()->createUploadErrorResponse($accept_language);
      $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $error_response);
      $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

      return $error_response;
    }

    // Setting the project's attributes
    $project->setPrivate($private);
    $this->facade->getProcessor()->saveProject($project);

    // Since we have come this far, the project upload is completed
    $responseCode = Response::HTTP_CREATED;
    $responseHeaders['Location'] = $this->facade->getResponseManager()->createProjectLocation($project);

    return $this->facade->getResponseManager()->createProjectDataResponse($project, 'ALL');
  }

  #[\Override]
  public function projectsSearchGet(string $query, string $max_version, int $limit, int $offset, string $attributes, string $flavor, int &$responseCode, array &$responseHeaders): array
  {
    $projects = $this->facade->getLoader()->searchProjects($query, $limit, $offset, $max_version, $flavor);

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createProjectsDataResponse($projects, $attributes);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  /**
   * @throws \JsonException
   * @throws \Psr\Cache\InvalidArgumentException
   */
  #[\Override]
  public function projectsCategoriesGet(string $max_version, string $flavor, string $accept_language, int &$responseCode, array &$responseHeaders): array
  {
    $limit = 20;
    $offset = 0;

    $rm = $this->facade->getResponseManager();
    $locale = $rm->sanitizeLocale($accept_language);

    $cache_id = sprintf(
      'projectsCategoriesGet_%s_%s_%s',
      $flavor,
      $locale,
      $max_version
    );

    $cached = $rm->getCachedResponse($cache_id);
    if (null !== $cached) {
      $responseCode = $cached['response_code'];
      $responseHeaders = $cached['response_headers'];

      return $cached['response'];
    }

    $response = [];
    $categories = ['recent', 'example', 'most_downloaded', 'random', 'scratch', 'trending'];

    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();

    foreach ($categories as $category) {
      $projects = $this->facade->getLoader()->getProjectsFromCategory(
        $category,
        $max_version,
        $limit,
        $offset,
        $flavor,
        $user
      );

      $response[] = $rm->createProjectCategoryResponse($projects, $category, $accept_language);
    }

    $responseHeaders = [];
    $rm->addResponseHashToHeaders($responseHeaders, $response);
    $rm->addContentLanguageToHeaders($responseHeaders);

    $responseCode = 200;

    $rm->cacheResponse($cache_id, $responseCode, $responseHeaders, $response);

    return $response;
  }

  #[\Override]
  public function projectsUserGet(string $max_version, int $limit, int $offset, string $attributes, string $flavor, int &$responseCode, array &$responseHeaders): ?array
  {
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    if (is_null($user)) {
      $responseCode = Response::HTTP_FORBIDDEN;

      return null;
    }

    $user_projects = $this->facade->getLoader()->getUserProjects($user->getId(), $limit, $offset, $flavor, $max_version);

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createProjectsDataResponse($user_projects, $attributes);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  #[\Override]
  public function projectsUserIdGet(string $id, string $max_version, int $limit, int $offset, string $attributes, string $flavor, int &$responseCode, array &$responseHeaders): ?array
  {
    if (!$this->facade->getRequestValidator()->validateUserExists($id)) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $projects = $this->facade->getLoader()->getUserPublicProjects($id, $limit, $offset, $flavor, $max_version);

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createProjectsDataResponse($projects, $attributes);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  #[\Override]
  public function projectIdReportPost(string $id, ProjectReportRequest $project_report_request, int &$responseCode, array &$responseHeaders): void
  {
    // TODO: Implement projectIdReportPost() method.

    $responseCode = Response::HTTP_NOT_IMPLEMENTED;
  }

  #[\Override]
  public function projectIdDelete(string $id, int &$responseCode, array &$responseHeaders): void
  {
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    if (is_null($user)) {
      $responseCode = Response::HTTP_UNAUTHORIZED;

      return;
    }

    $success = $this->facade->getProcessor()->deleteProjectById($id, $user);

    $responseCode = $success ? Response::HTTP_NO_CONTENT : Response::HTTP_NOT_FOUND;
  }

  #[\Override]
  public function projectsExtensionsGet(string $accept_language, int &$responseCode, array &$responseHeaders): array
  {
    $locale = $this->facade->getResponseManager()->sanitizeLocale($accept_language);

    $extensions = $this->facade->getLoader()->getProjectExtensions();

    $response = $this->facade->getResponseManager()->createProjectsExtensionsResponse($extensions, $locale);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);
    $responseCode = Response::HTTP_OK;

    return $response;
  }

  #[\Override]
  public function projectsTagsGet(string $accept_language, int &$responseCode, array &$responseHeaders): array
  {
    $locale = $this->facade->getResponseManager()->sanitizeLocale($accept_language);

    $tags = $this->facade->getLoader()->getProjectTags();

    $response = $this->facade->getResponseManager()->createProjectsTagsResponse($tags, $locale);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);
    $responseCode = Response::HTTP_OK;

    return $response;
  }

  #[\Override]
  public function projectIdCatrobatGet(string $id, int &$responseCode, array &$responseHeaders): array|object|null
  {
    // Currently not used due to an issue with the serializer and accept encoding in the generated code
    // The route is overwritten by the OverwriteController which uses the method: customProjectIdCatrobatGet
    return null;
  }

  /**
   * @psalm-param 200|404|500 $responseCode
   */
  public function customProjectIdCatrobatGet(string $id, int &$responseCode, ?array &$responseHeaders = null): ?BinaryFileResponse
  {
    $project = $this->facade->getLoader()->findProjectByID($id, true);
    if (!$project instanceof Program) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $zipFile = $this->facade->getLoader()->getProjectCatrobatZipFile($id);
    if (!$zipFile instanceof File) {
      $responseCode = Response::HTTP_INTERNAL_SERVER_ERROR;

      return null;
    }

    $response = $this->facade->getResponseManager()->createProjectCatrobatFileResponse($project->getId(), $zipFile);
    $responseCode = Response::HTTP_OK;

    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    $this->facade->getEventDispatcher()->dispatch(
      new ProjectDownloadEvent($user, $project, ProgramDownloads::TYPE_PROJECT)
    );

    return $response;
  }

  #[\Override]
  public function projectIdReactionPost(
    string $id,
    ReactionRequest $reaction_request,
    string $accept_language,
    int &$responseCode,
    array &$responseHeaders,
  ): array|object|null {
    $user = $this->reactions_facade->getAuthenticationManager()->getAuthenticatedUser();
    if (null === $user) {
      $responseCode = Response::HTTP_UNAUTHORIZED;

      return null;
    }

    $project = $this->reactions_facade->getLoader()->findProjectIfVisibleToCurrentUser($id, $user);
    if (null === $project) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $type_name = $reaction_request->getType();
    if (null === $type_name) {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;

      return null;
    }

    $type = ReactionsApiProcessor::getTypeFromName($type_name);
    if (null === $type) {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;

      return null;
    }

    $added = $this->reactions_facade->getProcessor()->addReaction($project, $user, $type);
    if (!$added) {
      $responseCode = Response::HTTP_CONFLICT;

      return null;
    }

    $counts = $this->reactions_facade->getLoader()->getReactionCounts($id);
    $user_reactions = $this->reactions_facade->getLoader()->getUserReactions($id, $user);

    $responseCode = Response::HTTP_CREATED;
    $response = $this->reactions_facade->getResponseManager()->createReactionSummaryResponse($counts, $user_reactions);
    $this->reactions_facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);

    return $response;
  }

  #[\Override]
  public function projectIdReactionDelete(
    string $id,
    string $type,
    string $accept_language,
    int &$responseCode,
    array &$responseHeaders,
  ): void {
    $user = $this->reactions_facade->getAuthenticationManager()->getAuthenticatedUser();
    if (null === $user) {
      $responseCode = Response::HTTP_UNAUTHORIZED;

      return;
    }

    $project = $this->reactions_facade->getLoader()->findProjectIfVisibleToCurrentUser($id, $user);
    if (null === $project) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return;
    }

    $type_int = ReactionsApiProcessor::getTypeFromName($type);
    if (null === $type_int) {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;

      return;
    }

    $this->reactions_facade->getProcessor()->removeReaction($project, $user, $type_int);
    $responseCode = Response::HTTP_NO_CONTENT;
  }

  #[\Override]
  public function projectIdReactionsGet(
    string $id,
    string $accept_language,
    int &$responseCode,
    array &$responseHeaders,
  ): array|object|null {
    $user = $this->reactions_facade->getAuthenticationManager()->getAuthenticatedUser();
    $project = $this->reactions_facade->getLoader()->findProjectIfVisibleToCurrentUser($id, $user);

    if (null === $project) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $counts = $this->reactions_facade->getLoader()->getReactionCounts($id);
    $user_reactions = null !== $user ? $this->reactions_facade->getLoader()->getUserReactions($id, $user) : [];

    $responseCode = Response::HTTP_OK;
    $response = $this->reactions_facade->getResponseManager()->createReactionSummaryResponse($counts, $user_reactions);
    $this->reactions_facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);

    return $response;
  }

  #[\Override]
  public function projectIdReactionsUsersGet(
    string $id,
    string $accept_language,
    ?string $type,
    int $limit,
    ?string $cursor,
    int &$responseCode,
    array &$responseHeaders,
  ): array|object|null {
    $user = $this->reactions_facade->getAuthenticationManager()->getAuthenticatedUser();
    $project = $this->reactions_facade->getLoader()->findProjectIfVisibleToCurrentUser($id, $user);

    if (null === $project) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $type_int = null !== $type ? ReactionsApiProcessor::getTypeFromName($type) : null;

    $paginated_data = $this->reactions_facade->getLoader()->getReactionUsersPaginated($id, $type_int, $limit, $cursor);

    $responseCode = Response::HTTP_OK;
    $response = $this->reactions_facade->getResponseManager()->createReactionUsersResponse($paginated_data);
    $this->reactions_facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);

    return $response;
  }
}
