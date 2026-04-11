<?php

declare(strict_types=1);

namespace App\Api;

use App\Api\Exceptions\ApiErrorResponse;
use App\Api\Services\Base\AbstractApiController;
use App\Api\Services\Projects\ProjectsApiFacade;
use App\Api\Services\Reactions\ReactionsApiFacade;
use App\Api\Services\Reactions\ReactionsApiProcessor;
use App\Api\Traits\CursorPaginationTrait;
use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\ProgramDownloads;
use App\Project\AddProjectRequest;
use App\Project\CatrobatFile\InvalidCatrobatFileException;
use App\Project\CodeView\CodeTreeBuilder;
use App\Project\CodeView\CodeTreeBuildException;
use App\Project\Event\ProjectDownloadEvent;
use OpenAPI\Server\Api\ProjectsApiInterface;
use OpenAPI\Server\Model\CodeViewResponse;
use OpenAPI\Server\Model\ExtensionsResponse;
use OpenAPI\Server\Model\FeaturedProjectsListResponse;
use OpenAPI\Server\Model\ProjectResponse;
use OpenAPI\Server\Model\ProjectsCategoryListResponse;
use OpenAPI\Server\Model\ProjectsListResponse;
use OpenAPI\Server\Model\ReactionRequest;
use OpenAPI\Server\Model\TagsResponse;
use OpenAPI\Server\Model\UpdateProjectRequest;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class ProjectsApi extends AbstractApiController implements ProjectsApiInterface
{
  use CursorPaginationTrait;
  use RateLimitTrait;

  public function __construct(
    private readonly ProjectsApiFacade $facade,
    private readonly ReactionsApiFacade $reactions_facade,
    private readonly CodeTreeBuilder $code_tree_builder,
    private readonly RateLimiterFactory $uploadDailyLimiter,
    private readonly RateLimiterFactory $reactionBurstLimiter,
    private readonly RateLimiterFactory $downloadBurstLimiter,
    private readonly RequestStack $request_stack,
  ) {
  }

  #[\Override]
  public function projectsIdGet(string $id, int &$responseCode, array &$responseHeaders): ?ProjectResponse
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
  public function projectsIdPatch(string $id, UpdateProjectRequest $update_project_request, string $accept_language, int &$responseCode, array &$responseHeaders): array|object|null
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
      $error_response = ApiErrorResponse::createValidationModel($validation_wrapper->getErrors());
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
    $error_response = $this->facade->getResponseManager()->createUpdateFailureResponse((int) $result, $accept_language);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $error_response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $error_response;
  }

  #[\Override]
  public function projectsFeaturedGet(string $platform, string $max_version, int $limit, ?string $cursor, string $attributes, string $flavor, int &$responseCode, array &$responseHeaders): FeaturedProjectsListResponse
  {
    $offset = $this->decodeCursorToOffset($cursor);
    $featured_projects = $this->facade->getLoader()->getFeaturedProjects($flavor, $limit + 1, $offset, $platform, $max_version);

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createFeaturedProjectsListResponse($featured_projects, $limit, $attributes, $offset);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  /**
   * @throws \JsonException
   * @throws \Psr\Cache\InvalidArgumentException
   */
  #[\Override]
  public function projectsGet(string $accept_language, ?string $category, string $max_version, int $limit, ?string $cursor, string $attributes, string $flavor, int &$responseCode, array &$responseHeaders): ProjectsListResponse
  {
    $category = $category ?? 'recent';
    $offset = $this->decodeCursorToOffset($cursor);
    $locale = $this->facade->getResponseManager()->sanitizeLocale($accept_language);
    $cache_id = sprintf('projectsGet_%s_%s_%s_%s_%d_%d', $category, $locale, $flavor, $max_version, $limit, $offset);

    // Don't cache 'recent' category as it changes frequently
    if ('recent' === $category) {
      $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
      $projects = $this->facade->getLoader()->getProjectsFromCategory($category, $max_version, $limit + 1, $offset, $flavor, $user);

      $responseCode = Response::HTTP_OK;
      $response = $this->facade->getResponseManager()->createProjectsListResponse($projects, $limit, $attributes, $offset);
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
    $projects = $this->facade->getLoader()->getProjectsFromCategory($category, $max_version, $limit + 1, $offset, $flavor, $user);

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createProjectsListResponse($projects, $limit, $attributes, $offset);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);
    $this->facade->getResponseManager()->cacheResponse($cache_id, $responseCode, $responseHeaders, $response);

    return $response;
  }

  #[\Override]
  public function projectsIdRecommendationsGet(string $id, string $category, string $accept_language, string $max_version, int $limit, ?string $cursor, string $attributes, string $flavor, int &$responseCode, array &$responseHeaders): ?ProjectsListResponse
  {
    $offset = $this->decodeCursorToOffset($cursor);
    $project = $this->facade->getLoader()->findProjectByID($id, true);
    if (is_null($project)) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $recommended_projects = $this->facade->getLoader()->getRecommendedProjects(
      $id, $category, $max_version, $limit + 1, $offset, $flavor, $this->facade->getAuthenticationManager()->getAuthenticatedUser()
    );

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createProjectsListResponse($recommended_projects, $limit, $attributes, $offset);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  #[\Override]
  public function projectsPost(string $checksum, UploadedFile $file, string $accept_language, string $flavor, bool $private, int &$responseCode, array &$responseHeaders): array|object|null
  {
    // Getting the user who uploaded
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();

    if ($user instanceof \App\DB\Entity\User\User && null === $this->checkUserRateLimit($user, $this->uploadDailyLimiter)) {
      $responseCode = Response::HTTP_TOO_MANY_REQUESTS;

      return null;
    }

    $validation_wrapper = $this->facade->getRequestValidator()->validateUploadFile($checksum, $file, $accept_language);
    if ($validation_wrapper->hasError()) {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;
      $errors = $validation_wrapper->getErrors();
      $first_message = reset($errors) ?: 'Upload validation failed';
      $error_response = ApiErrorResponse::createModel(422, 'validation_error', $first_message);
      $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $error_response);
      $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

      return $error_response;
    }

    if (!$user instanceof \App\DB\Entity\User\User) {
      $responseCode = Response::HTTP_UNAUTHORIZED;

      return null;
    }

    // Needed (for tests) to make sure everything is up to date (followers, ..)
    $this->facade->getProcessor()->refreshUser($user);

    try {
      $project = $this->facade->getProcessor()->addProject(
        new AddProjectRequest(
          $user, $file, $this->facade->getLoader()->getClientIp(), $accept_language, $flavor
        )
      );
    } catch (InvalidCatrobatFileException $e) {
      $this->facade->getLogger()->warning('Project upload rejected: '.$e->getMessage(), [
        'debug' => $e->getDebugMessage(),
      ]);
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;
      $error_response = $this->facade->getResponseManager()->createUploadValidationErrorResponse($e->getMessage(), $accept_language);
      $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $error_response);
      $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

      return $error_response;
    } catch (\Exception $e) {
      $this->facade->getLogger()->critical('Project Upload broken: '.$e->getMessage().$e->getTraceAsString());
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;
      $error_response = $this->facade->getResponseManager()->createUploadErrorResponse($accept_language);
      $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $error_response);
      $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

      return $error_response;
    }

    if (!$project instanceof Program) {
      $responseCode = Response::HTTP_INTERNAL_SERVER_ERROR;

      return null;
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
  public function projectsSearchGet(string $query, string $max_version, int $limit, ?string $cursor, string $attributes, string $flavor, int &$responseCode, array &$responseHeaders): ProjectsListResponse
  {
    // Elasticsearch: offset-based pagination (cursor decoded to offset)
    $offset = $this->decodeCursorToOffset($cursor);
    $projects = $this->facade->getLoader()->searchProjects($query, $limit + 1, $offset, $max_version, $flavor);

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createProjectsListResponse($projects, $limit, $attributes, $offset);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  /**
   * @throws \JsonException
   * @throws \Psr\Cache\InvalidArgumentException
   */
  #[\Override]
  public function projectsCategoriesGet(string $max_version, string $flavor, string $accept_language, int &$responseCode, array &$responseHeaders): ProjectsCategoryListResponse
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

    $categories_data = [];
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

      $categories_data[] = $rm->createProjectCategoryResponse($projects, $category, $accept_language);
    }

    $response = new ProjectsCategoryListResponse();
    $response->setData($categories_data);

    $responseHeaders = [];
    $rm->addResponseHashToHeaders($responseHeaders, $response);
    $rm->addContentLanguageToHeaders($responseHeaders);

    $responseCode = 200;

    $rm->cacheResponse($cache_id, $responseCode, $responseHeaders, $response);

    return $response;
  }

  #[\Override]
  public function projectsUserGet(string $max_version, int $limit, ?string $cursor, string $attributes, string $flavor, int &$responseCode, array &$responseHeaders): ?ProjectsListResponse
  {
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    if (is_null($user)) {
      $responseCode = Response::HTTP_FORBIDDEN;

      return null;
    }

    $user_id = $user->getId();
    if (null === $user_id) {
      $responseCode = Response::HTTP_INTERNAL_SERVER_ERROR;

      return null;
    }

    $offset = $this->decodeCursorToOffset($cursor);
    $user_projects = $this->facade->getLoader()->getUserProjects($user_id, $limit + 1, $offset, $flavor, $max_version);

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createProjectsListResponse($user_projects, $limit, $attributes, $offset);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  #[\Override]
  public function projectsUserIdGet(string $id, string $max_version, int $limit, ?string $cursor, string $attributes, string $flavor, int &$responseCode, array &$responseHeaders): ?ProjectsListResponse
  {
    if (!$this->facade->getRequestValidator()->validateUserExists($id)) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $offset = $this->decodeCursorToOffset($cursor);
    $projects = $this->facade->getLoader()->getUserPublicProjects($id, $limit + 1, $offset, $flavor, $max_version);

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createProjectsListResponse($projects, $limit, $attributes, $offset);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  #[\Override]
  public function projectsIdDelete(string $id, int &$responseCode, array &$responseHeaders): void
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
  public function projectsExtensionsGet(string $accept_language, int &$responseCode, array &$responseHeaders): ExtensionsResponse
  {
    $locale = $this->facade->getResponseManager()->sanitizeLocale($accept_language);

    $extensions = $this->facade->getLoader()->getProjectExtensions();

    $extension_items = $this->facade->getResponseManager()->createProjectsExtensionsResponse($extensions, $locale);

    $response = new ExtensionsResponse();
    $response->setData($extension_items);

    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);
    $responseCode = Response::HTTP_OK;

    return $response;
  }

  #[\Override]
  public function projectsTagsGet(string $accept_language, int &$responseCode, array &$responseHeaders): TagsResponse
  {
    $locale = $this->facade->getResponseManager()->sanitizeLocale($accept_language);

    $tags = $this->facade->getLoader()->getProjectTags();

    $tag_items = $this->facade->getResponseManager()->createProjectsTagsResponse($tags, $locale);

    $response = new TagsResponse();
    $response->setData($tag_items);

    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);
    $responseCode = Response::HTTP_OK;

    return $response;
  }

  #[\Override]
  public function projectsIdCatrobatGet(string $id, int &$responseCode, array &$responseHeaders): array|object|null
  {
    // Currently not used due to an issue with the serializer and accept encoding in the generated code
    // The route is overwritten by the OverwriteController which uses the method: customProjectIdCatrobatGet
    return null;
  }

  /**
   * @psalm-param 200|404|429|500 $responseCode
   */
  public function customProjectsIdCatrobatGet(string $id, int &$responseCode, ?array &$responseHeaders = null): ?BinaryFileResponse
  {
    $ip = $this->request_stack->getCurrentRequest()?->getClientIp() ?? 'unknown';
    if (null === $this->checkIpRateLimit($ip, $this->downloadBurstLimiter)) {
      $responseCode = Response::HTTP_TOO_MANY_REQUESTS;

      return null;
    }

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

    $project_id = $project->getId();
    if (null === $project_id) {
      $responseCode = Response::HTTP_INTERNAL_SERVER_ERROR;

      return null;
    }
    $response = $this->facade->getResponseManager()->createProjectCatrobatFileResponse($project_id, $zipFile);
    $responseCode = Response::HTTP_OK;

    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    $this->facade->getEventDispatcher()->dispatch(
      new ProjectDownloadEvent($user, $project, ProgramDownloads::TYPE_PROJECT)
    );

    return $response;
  }

  #[\Override]
  public function projectsIdReactionPost(
    string $id,
    ReactionRequest $reaction_request,
    string $accept_language,
    int &$responseCode,
    array &$responseHeaders,
  ): array|object|null {
    $user = $this->reactions_facade->getAuthenticationManager()->getAuthenticatedUser();
    if (!$user instanceof \App\DB\Entity\User\User) {
      $responseCode = Response::HTTP_UNAUTHORIZED;

      return null;
    }

    if (null === $this->checkUserRateLimit($user, $this->reactionBurstLimiter)) {
      $responseCode = Response::HTTP_TOO_MANY_REQUESTS;

      return null;
    }

    $project = $this->reactions_facade->getLoader()->findProjectIfVisibleToCurrentUser($id, $user);
    if (!$project instanceof Program) {
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
  public function projectsIdReactionDelete(
    string $id,
    string $type,
    string $accept_language,
    int &$responseCode,
    array &$responseHeaders,
  ): void {
    $user = $this->reactions_facade->getAuthenticationManager()->getAuthenticatedUser();
    if (!$user instanceof \App\DB\Entity\User\User) {
      $responseCode = Response::HTTP_UNAUTHORIZED;

      return;
    }

    $project = $this->reactions_facade->getLoader()->findProjectIfVisibleToCurrentUser($id, $user);
    if (!$project instanceof Program) {
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
  public function projectsIdReactionsGet(
    string $id,
    string $accept_language,
    int &$responseCode,
    array &$responseHeaders,
  ): array|object|null {
    $user = $this->reactions_facade->getAuthenticationManager()->getAuthenticatedUser();
    $project = $this->reactions_facade->getLoader()->findProjectIfVisibleToCurrentUser($id, $user);

    if (!$project instanceof Program) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $counts = $this->reactions_facade->getLoader()->getReactionCounts($id);
    $user_reactions = $user instanceof \App\DB\Entity\User\User ? $this->reactions_facade->getLoader()->getUserReactions($id, $user) : [];

    $responseCode = Response::HTTP_OK;
    $response = $this->reactions_facade->getResponseManager()->createReactionSummaryResponse($counts, $user_reactions);
    $this->reactions_facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);

    return $response;
  }

  #[\Override]
  public function projectsIdReactionsUsersGet(
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

    if (!$project instanceof Program) {
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

  #[\Override]
  public function projectsIdCodeGet(
    string $id,
    int &$responseCode,
    array &$responseHeaders,
  ): array|object|null {
    $project = $this->facade->getLoader()->findProjectByID($id, true);
    if (null === $project) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    try {
      $tree = $this->code_tree_builder->buildCodeTree($project);
    } catch (CodeTreeBuildException) {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;

      return null;
    }

    $responseCode = Response::HTTP_OK;
    $response = new CodeViewResponse($tree);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);

    return $response;
  }

  #[\Override]
  public function projectsIdCodeStatisticsGet(
    string $id,
    int &$responseCode,
    array &$responseHeaders,
  ): array|object|null {
    $project = $this->facade->getLoader()->findProjectByID($id, true);
    if (null === $project) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $stats = $this->facade->getLoader()->getCodeStatistics($project);
    if (null === $stats) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $responseCode = Response::HTTP_OK;

    return $this->facade->getResponseManager()->createCodeStatisticsResponse($stats);
  }
}
