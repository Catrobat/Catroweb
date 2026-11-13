<?php

declare(strict_types=1);

namespace App\Api;

use App\Api\Exceptions\ApiErrorResponse;
use App\Api\Services\Base\AbstractApiController;
use App\Api\Services\Projects\ProjectsApiFacade;
use App\Api\Services\Reactions\ReactionsApiFacade;
use App\Api\Services\Reactions\ReactionsApiProcessor;
use App\Api\Traits\CursorPaginationTrait;
use App\Api\Traits\KeysetCursorTrait;
use App\DB\Entity\Project\Project;
use App\DB\Entity\Project\ProjectDownloads;
use App\DB\Entity\User\User;
use App\Project\AddProjectRequest;
use App\Project\CatrobatFile\InvalidCatrobatFileException;
use App\Project\CodeView\CodeExportUnavailableException;
use App\Project\CodeView\CodeTreeBuilder;
use App\Project\CodeView\CodeTreeBuildException;
use App\Project\Event\ProjectDownloadEvent;
use App\Project\Remix\RemixManager;
use App\Project\Scratch\Converter\ScratchConversionException;
use App\Project\Scratch\ScratchManager;
use App\Project\Upload\ResumableUpload;
use App\Project\Upload\ResumableUploadException;
use App\Project\Upload\ResumableUploadStore;
use App\Storage\ScreenshotRepository;
use OpenAPI\Server\Api\ProjectsApiInterface;
use OpenAPI\Server\Model\CodeViewResponse;
use OpenAPI\Server\Model\ErrorResponse;
use OpenAPI\Server\Model\ErrorResponseError;
use OpenAPI\Server\Model\ExtensionsResponse;
use OpenAPI\Server\Model\FeaturedProjectsListResponse;
use OpenAPI\Server\Model\ProjectResponse;
use OpenAPI\Server\Model\ProjectsCategoryListResponse;
use OpenAPI\Server\Model\ProjectsListResponse;
use OpenAPI\Server\Model\ProjectUploadSessionRequest;
use OpenAPI\Server\Model\ProjectUploadSessionResponse;
use OpenAPI\Server\Model\ReactionRequest;
use OpenAPI\Server\Model\TagsResponse;
use OpenAPI\Server\Model\UpdateProjectRequest;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface as RateLimiterFactory;

class ProjectsApi extends AbstractApiController implements ProjectsApiInterface
{
  use CursorPaginationTrait;
  use KeysetCursorTrait;
  use RateLimitTrait;

  private const string SCRATCH_THUMBNAIL_URL_TEMPLATE = 'https://cdn2.scratch.mit.edu/get_image/project/%s_140x140.png';
  private const string IMAGE_NOT_AVAILABLE_URL = '/images/default/not_available.png';

  public function __construct(
    private readonly ProjectsApiFacade $facade,
    private readonly ReactionsApiFacade $reactions_facade,
    private readonly CodeTreeBuilder $code_tree_builder,
    private readonly RemixManager $remix_manager,
    private readonly ScreenshotRepository $screenshot_repository,
    private readonly RateLimiterFactory $uploadDailyLimiter,
    private readonly RateLimiterFactory $reactionBurstLimiter,
    private readonly RateLimiterFactory $downloadBurstLimiter,
    private readonly RequestStack $request_stack,
    private readonly LoggerInterface $logger,
    private readonly RateLimiterFactory $scratchOpenBurstLimiter,
    private readonly RateLimiterFactory $scratchConvertBurstLimiter,
    private readonly RateLimiterFactory $scratchConvertDailyLimiter,
    private readonly ScratchManager $scratch_manager,
    private readonly ResumableUploadStore $resumable_uploads,
    // Channel "upload": one line per upload attempt, kept at info level so rejections reach the log.
    private readonly LoggerInterface $uploadLogger = new NullLogger(),
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

    $validation_wrapper = $this->facade->getRequestValidator()->validateUpdateRequest($update_project_request, $accept_language, $project);

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
    $cursor_data = $this->decodeIntKeysetCursor($cursor);
    if (null === $cursor_data && null !== $cursor && '' !== $cursor) {
      $responseCode = Response::HTTP_BAD_REQUEST;

      return new FeaturedProjectsListResponse(['data' => [], 'next_cursor' => null, 'has_more' => false]);
    }

    $featured_projects = $this->facade->getLoader()->getFeaturedProjectsKeyset(
      $flavor,
      $limit + 1,
      $platform,
      $max_version,
      $cursor_data['value'] ?? null,
      isset($cursor_data['id']) ? (int) $cursor_data['id'] : null
    );

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createFeaturedProjectsKeysetResponse($featured_projects, $limit, $attributes);
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

    // Categories that must stay offset-based (Elasticsearch, random, or no stable sort)
    if (in_array($category, ['random', 'example', 'scratch', 'trending', 'popular'], true)) {
      $offset = $this->decodeCursorToOffset($cursor);
      $locale = $this->facade->getResponseManager()->sanitizeLocale($accept_language);
      $cache_id = $this->sharedListCacheId(sprintf('projectsGet_%s_%s_%s_%s_%d_%d_%s', $category, $locale, $flavor, $max_version, $limit, $offset, md5($attributes)));

      $cached = null === $cache_id ? null : $this->facade->getResponseManager()->getCachedResponse($cache_id);
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
      if (null !== $cache_id) {
        $this->facade->getResponseManager()->cacheResponse($cache_id, $responseCode, $responseHeaders, $response);
      }

      return $response;
    }

    // Keyset cursor pagination for recent, most_viewed, most_downloaded
    $sort_by = match ($category) {
      'most_viewed' => 'views',
      'most_downloaded' => 'downloads',
      default => 'uploaded_at',
    };

    $cursor_date = null;
    $cursor_value = null;
    $cursor_id = null;

    if (null !== $cursor && '' !== $cursor) {
      if ('uploaded_at' === $sort_by) {
        $cursor_data = $this->decodeDateKeysetCursor($cursor);
        if (null === $cursor_data) {
          $responseCode = Response::HTTP_BAD_REQUEST;

          return new ProjectsListResponse(['data' => [], 'next_cursor' => null, 'has_more' => false]);
        }
        $cursor_date = $cursor_data['date'];
        $cursor_id = $cursor_data['id'];
      } else {
        $cursor_data = $this->decodeIntKeysetCursor($cursor);
        if (null === $cursor_data) {
          $responseCode = Response::HTTP_BAD_REQUEST;

          return new ProjectsListResponse(['data' => [], 'next_cursor' => null, 'has_more' => false]);
        }
        $cursor_value = $cursor_data['value'];
        $cursor_id = $cursor_data['id'];
      }
    }

    // Cache most_viewed/most_downloaded but not recent (changes too frequently)
    $cache_id = null;
    if ('recent' !== $category) {
      $locale = $this->facade->getResponseManager()->sanitizeLocale($accept_language);
      $cache_id = $this->sharedListCacheId(sprintf('projectsGet_%s_%s_%s_%s_%d_%s_%s', $category, $locale, $flavor, $max_version, $limit, $cursor ?? '', md5($attributes)));
    }
    if (null !== $cache_id) {
      $cached = $this->facade->getResponseManager()->getCachedResponse($cache_id);
      if (null !== $cached) {
        $responseCode = $cached['response_code'];
        $responseHeaders = $cached['response_headers'];

        return $cached['response'];
      }
    }

    $projects = $this->facade->getLoader()->getProjectsKeyset(
      $category,
      $max_version,
      $limit + 1,
      $flavor,
      $cursor_date,
      $cursor_value,
      $cursor_id
    );

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createProjectsKeysetResponse($projects, $limit, $sort_by, $attributes);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    if (null !== $cache_id) {
      $this->facade->getResponseManager()->cacheResponse($cache_id, $responseCode, $responseHeaders, $response);
    }

    return $response;
  }

  #[\Override]
  public function projectsIdRecommendationsGet(string $id, string $category, string $accept_language, string $max_version, int $limit, ?string $cursor, string $attributes, string $flavor, int &$responseCode, array &$responseHeaders): ?ProjectsListResponse
  {
    $project = $this->facade->getLoader()->findProjectByID($id, true);
    if (is_null($project)) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $cursor_date = null;
    $cursor_id = null;
    if (null !== $cursor && '' !== $cursor) {
      $cursor_data = $this->decodeDateKeysetCursor($cursor);
      if (null === $cursor_data) {
        $responseCode = Response::HTTP_BAD_REQUEST;

        return new ProjectsListResponse(['data' => [], 'next_cursor' => null, 'has_more' => false]);
      }
      $cursor_date = $cursor_data['date'];
      $cursor_id = $cursor_data['id'];
    }

    $recommended_projects = $this->facade->getLoader()->getRecommendedProjectsKeyset(
      $id,
      $category,
      $max_version,
      $limit + 1,
      $flavor,
      $cursor_date,
      $cursor_id
    );

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createProjectsKeysetResponse($recommended_projects, $limit, 'uploaded_at', $attributes);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  #[\Override]
  public function projectsPost(string $checksum, UploadedFile $file, string $accept_language, string $flavor, bool $private, ?string $project_id, int &$responseCode, array &$responseHeaders): array|object|null
  {
    $started = microtime(true);
    // Getting the user who uploaded
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();

    if ($user instanceof User && null === $this->checkUserRateLimit($user, $this->uploadDailyLimiter)) {
      $responseCode = Response::HTTP_TOO_MANY_REQUESTS;
      $this->logUploadOutcome('rate_limited', null, $file, $flavor, $started);

      return null;
    }

    $validation_wrapper = $this->facade->getRequestValidator()->validateUploadFile($checksum, $file, $accept_language);
    if ($validation_wrapper->hasError()) {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;
      $errors = $validation_wrapper->getErrors();
      $first_message = reset($errors) ?: 'Upload validation failed';
      $this->logUploadOutcome('rejected', (string) $first_message, $file, $flavor, $started);
      $error_response = ApiErrorResponse::createModel(422, 'validation_error', $first_message);
      $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $error_response);
      $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

      return $error_response;
    }

    if (!$user instanceof User) {
      $responseCode = Response::HTTP_UNAUTHORIZED;
      $this->logUploadOutcome('unauthorized', null, $file, $flavor, $started);

      return null;
    }

    return $this->createProjectFromFile($user, $file, $accept_language, $flavor, $private, $project_id, $started, 'single', $responseCode, $responseHeaders);
  }

  #[\Override]
  public function projectsScratchScratchIdPost(int $scratch_id, int &$responseCode, array &$responseHeaders): array|object|null
  {
    // Every request asks Scratch's API, even when our copy is current; converting is limited much harder.
    if ($this->isCrossSiteRequest()) {
      $responseCode = Response::HTTP_FORBIDDEN;

      return ApiErrorResponse::createModel($responseCode, 'forbidden', 'Scratch projects can only be imported from this site.');
    }

    $ip = $this->facade->getLoader()->getClientIp() ?? 'unknown';
    $converted = false;
    try {
      if (null === $this->checkIpRateLimit($ip, $this->scratchOpenBurstLimiter)) {
        throw new ScratchConversionException('Too many Scratch projects opened in a short time. Please wait a minute.', ScratchConversionException::RATE_LIMITED);
      }

      $project = $this->scratch_manager->importProject($scratch_id, function () use ($ip, &$converted): void {
        $burst = $this->scratchConvertBurstLimiter->create($ip)->consume();
        $daily = $this->scratchConvertDailyLimiter->create($ip)->consume();
        if (!$burst->isAccepted() || !$daily->isAccepted()) {
          throw new ScratchConversionException('You converted many Scratch projects in a short time. Please try again later.', ScratchConversionException::RATE_LIMITED);
        }

        $converted = true;
      }, $ip);
    } catch (ScratchConversionException $exception) {
      [$responseCode, $type] = match ($exception->getCode()) {
        ScratchConversionException::NOT_FOUND => [Response::HTTP_NOT_FOUND, 'not_found'],
        ScratchConversionException::RATE_LIMITED => [Response::HTTP_TOO_MANY_REQUESTS, 'too_many_requests'],
        ScratchConversionException::BUSY => [Response::HTTP_SERVICE_UNAVAILABLE, 'service_unavailable'],
        ScratchConversionException::UPSTREAM => [Response::HTTP_BAD_GATEWAY, 'bad_gateway'],
        ScratchConversionException::INTERNAL => [Response::HTTP_INTERNAL_SERVER_ERROR, 'internal_server_error'],
        default => [Response::HTTP_UNPROCESSABLE_ENTITY, 'unprocessable_entity'],
      };
      if (Response::HTTP_SERVICE_UNAVAILABLE === $responseCode) {
        $responseHeaders['Retry-After'] = '60';
      }

      return ApiErrorResponse::createModel($responseCode, $type, $exception->getMessage());
    }

    $responseCode = $converted ? Response::HTTP_CREATED : Response::HTTP_OK;
    if ($converted) {
      $responseHeaders['Location'] = $this->facade->getResponseManager()->createProjectLocation($project);
    }

    return $this->facade->getResponseManager()->createProjectDataResponse($project, 'ALL');
  }

  /**
   * The import needs no login, so a form on another site could make visitors import projects and use up their quota.
   * Browsers say where a request comes from; apps and scripts send no Origin and are allowed. Hosts, not schemes, are
   * compared: behind a TLS-terminating proxy the request may look like plain http.
   */
  private function isCrossSiteRequest(): bool
  {
    $request = $this->request_stack->getCurrentRequest();
    if (null === $request) {
      return false;
    }

    if ('cross-site' === $request->headers->get('Sec-Fetch-Site')) {
      return true;
    }

    $origin = $request->headers->get('Origin');

    return null !== $origin && strtolower((string) parse_url($origin, PHP_URL_HOST)) !== strtolower($request->getHost());
  }

  #[\Override]
  public function projectsSearchGet(string $query, string $max_version, int $limit, ?string $cursor, string $attributes, string $flavor, int &$responseCode, array &$responseHeaders): ProjectsListResponse
  {
    // Elasticsearch only supports offset-based pagination (from/size), not keyset cursors
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

    $cache_id = $this->sharedListCacheId(sprintf(
      // v2: entries from before minors bypassed this cache may hold a minor's filtered lists.
      'projectsCategoriesGet_v2_%s_%s_%s',
      $flavor,
      $locale,
      $max_version
    ));

    $cached = null === $cache_id ? null : $rm->getCachedResponse($cache_id);
    if (null !== $cached) {
      $responseCode = $cached['response_code'];
      $responseHeaders = $cached['response_headers'];

      return $cached['response'];
    }

    $categories_data = [];
    $categories = ['popular', 'random', 'trending'];

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

    if (null !== $cache_id) {
      $rm->cacheResponse($cache_id, $responseCode, $responseHeaders, $response);
    }

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

    $cursor_date = null;
    $cursor_id = null;
    if (null !== $cursor && '' !== $cursor) {
      $cursor_data = $this->decodeDateKeysetCursor($cursor);
      if (null === $cursor_data) {
        $responseCode = Response::HTTP_BAD_REQUEST;

        return new ProjectsListResponse(['data' => [], 'next_cursor' => null, 'has_more' => false]);
      }
      $cursor_date = $cursor_data['date'];
      $cursor_id = $cursor_data['id'];
    }

    $user_projects = $this->facade->getLoader()->getUserProjectsKeyset($user_id, $limit + 1, $flavor, $max_version, $cursor_date, $cursor_id);

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createProjectsKeysetResponse($user_projects, $limit, 'uploaded_at', $attributes);
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

    $cursor_date = null;
    $cursor_id = null;
    if (null !== $cursor && '' !== $cursor) {
      $cursor_data = $this->decodeDateKeysetCursor($cursor);
      if (null === $cursor_data) {
        $responseCode = Response::HTTP_BAD_REQUEST;

        return new ProjectsListResponse(['data' => [], 'next_cursor' => null, 'has_more' => false]);
      }
      $cursor_date = $cursor_data['date'];
      $cursor_id = $cursor_data['id'];
    }

    $projects = $this->facade->getLoader()->getUserPublicProjectsKeyset($id, $limit + 1, $flavor, $max_version, $cursor_date, $cursor_id);

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createProjectsKeysetResponse($projects, $limit, 'uploaded_at', $attributes);
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
    $responseHeaders['Cache-Control'] = 'public, max-age=3600';
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
    $responseHeaders['Cache-Control'] = 'public, max-age=3600';
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
    if (!$project instanceof Project) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    try {
      $zipFile = $this->facade->getLoader()->getProjectCatrobatZipFile($id);
    } catch (FileNotFoundException) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    if (!$zipFile instanceof File) {
      $responseCode = Response::HTTP_INTERNAL_SERVER_ERROR;

      return null;
    }

    $project_id = $project->getId();
    if (null === $project_id) {
      $responseCode = Response::HTTP_INTERNAL_SERVER_ERROR;

      return null;
    }
    $response = $this->facade->getResponseManager()->createProjectCatrobatFileResponse($project_id, $zipFile, $project->getName());
    $responseCode = Response::HTTP_OK;

    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    if (true !== $this->request_stack->getCurrentRequest()?->isMethod('HEAD')) {
      $this->facade->getEventDispatcher()->dispatch(
        new ProjectDownloadEvent($user, $project, ProjectDownloads::TYPE_PROJECT)
      );
    }

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
    if (!$user instanceof User) {
      $responseCode = Response::HTTP_UNAUTHORIZED;

      return null;
    }

    if (null === $this->checkUserRateLimit($user, $this->reactionBurstLimiter)) {
      $responseCode = Response::HTTP_TOO_MANY_REQUESTS;

      return null;
    }

    $project = $this->reactions_facade->getLoader()->findProjectIfVisibleToCurrentUser($id, $user);
    if (!$project instanceof Project) {
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
    if (!$user instanceof User) {
      $responseCode = Response::HTTP_UNAUTHORIZED;

      return;
    }

    $project = $this->reactions_facade->getLoader()->findProjectIfVisibleToCurrentUser($id, $user);
    if (!$project instanceof Project) {
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

    if (!$project instanceof Project) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $counts = $this->reactions_facade->getLoader()->getReactionCounts($id);
    $user_reactions = $user instanceof User ? $this->reactions_facade->getLoader()->getUserReactions($id, $user) : [];

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

    if (!$project instanceof Project) {
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
    } catch (CodeExportUnavailableException) {
      // Being made, or the export could not run (CodeExportUnavailableException); the page offers to try again.
      $responseCode = Response::HTTP_SERVICE_UNAVAILABLE;
      $responseHeaders['Retry-After'] = '5';

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

  #[\Override]
  public function projectsIdRemixGraphGet(
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
      $remix_graph = $this->remix_manager->getRenderableRemixGraph($id);
    } catch (\Throwable $e) {
      $this->logger->error('Failed to build remix graph for project {id}: {message}', [
        'id' => $id,
        'message' => $e->getMessage(),
        'exception' => $e,
      ]);
      $responseCode = Response::HTTP_INTERNAL_SERVER_ERROR;

      return null;
    }

    $viewer = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    $nodes = array_map(function (array $node) use ($viewer): array {
      $owner_id = $node['_ownerId'] ?? null;
      $is_owner = \is_string($owner_id) && '' !== $owner_id && $owner_id === $viewer?->getId();
      unset($node['_ownerId']);
      if ($viewer?->isMinor() && ($node['notForKids'] ?? false) && !$is_owner) {
        $node['available'] = false;
        $node['name'] = null;
        $node['username'] = null;
      }

      // vis-network draws node images on a canvas, so not-for-kids thumbnails can't be CSS-blurred
      if (!$node['available'] || ($node['notForKids'] ?? false)) {
        $node['thumbnailUrl'] = self::IMAGE_NOT_AVAILABLE_URL;

        return $node;
      }

      if ('scratch' === $node['source']) {
        $node['thumbnailUrl'] = sprintf(self::SCRATCH_THUMBNAIL_URL_TEMPLATE, $node['projectId']);

        return $node;
      }

      $node['thumbnailUrl'] = '/'.$this->screenshot_repository->getThumbnailWebPath($node['projectId']);

      return $node;
    }, $remix_graph['nodes']);

    $responseCode = Response::HTTP_OK;
    $responseHeaders['Cache-Control'] = 'private, no-store';

    return [
      ...$remix_graph,
      'nodes' => $nodes,
    ];
  }

  /**
   * Cached project lists are shared by every viewer, but a minor's lists drop not-for-kids projects
   * (except their own), so those lists are never read from or written to the shared cache.
   */
  private function sharedListCacheId(string $cache_id): ?string
  {
    return true === $this->facade->getAuthenticationManager()->getAuthenticatedUser()?->isMinor() ? null : $cache_id;
  }

  #[\Override]
  public function projectsUploadsPost(ProjectUploadSessionRequest $project_upload_session_request, int &$responseCode, array &$responseHeaders): array|object|null
  {
    $started = microtime(true);
    $flavor = $project_upload_session_request->getFlavor() ?? 'pocketcode';
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    if (!$user instanceof User) {
      $responseCode = Response::HTTP_UNAUTHORIZED;

      return null;
    }

    if (null === $this->checkUserRateLimit($user, $this->uploadDailyLimiter)) {
      $responseCode = Response::HTTP_TOO_MANY_REQUESTS;
      $this->logUploadOutcome('rate_limited', null, null, $flavor, $started, 'resumable');

      return null;
    }

    try {
      $upload = $this->resumable_uploads->start(
        $user,
        (int) $project_upload_session_request->getFileSize(),
        $project_upload_session_request->getChecksum(),
        $flavor,
        (bool) $project_upload_session_request->isPrivate(),
        $project_upload_session_request->getProjectId(),
      );
    } catch (ResumableUploadException $e) {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;
      $this->logUploadOutcome('rejected', $e->getMessage(), null, $flavor, $started, 'resumable');

      return ApiErrorResponse::createModel(Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage(), $this->resumableUploadErrorText($e));
    }

    $responseCode = Response::HTTP_CREATED;

    return $this->createUploadSessionResponse($upload);
  }

  #[\Override]
  public function projectsUploadsIdGet(string $id, int &$responseCode, array &$responseHeaders): array|object|null
  {
    $upload = $this->findOwnUpload($id, $responseCode);
    if (!$upload instanceof ResumableUpload) {
      return null;
    }

    $responseCode = Response::HTTP_OK;

    return $this->createUploadSessionResponse($upload);
  }

  #[\Override]
  public function projectsUploadsIdDelete(string $id, int &$responseCode, array &$responseHeaders): void
  {
    $upload = $this->findOwnUpload($id, $responseCode);
    if (!$upload instanceof ResumableUpload) {
      return;
    }

    $lock = $this->resumable_uploads->lock($upload);
    if (null === $lock) {
      $responseCode = Response::HTTP_CONFLICT;

      return;
    }

    try {
      $this->resumable_uploads->delete($upload);
    } finally {
      $this->resumable_uploads->unlock($lock);
    }

    $responseCode = Response::HTTP_NO_CONTENT;
  }

  #[\Override]
  public function projectsUploadsIdChunksIndexPost(string $id, int $index, UploadedFile $chunk, string $checksum, int &$responseCode, array &$responseHeaders): void
  {
    $upload = $this->findOwnUpload($id, $responseCode);
    if (!$upload instanceof ResumableUpload) {
      return;
    }

    if (!$chunk->isValid()) {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;

      return;
    }

    try {
      $this->resumable_uploads->storeChunk($upload, $index, $chunk, $checksum);
    } catch (ResumableUploadException $e) {
      $responseCode = in_array($e->getMessage(), [ResumableUploadException::BUSY, ResumableUploadException::FINISHED], true)
        ? Response::HTTP_CONFLICT
        : Response::HTTP_UNPROCESSABLE_ENTITY;

      return;
    }

    $responseCode = Response::HTTP_NO_CONTENT;
  }

  #[\Override]
  public function projectsUploadsIdCompletePost(string $id, int &$responseCode, array &$responseHeaders): array|object|null
  {
    $started = microtime(true);
    $upload = $this->findOwnUpload($id, $responseCode, $user);
    if (!$upload instanceof ResumableUpload || !$user instanceof User) {
      return null;
    }

    $lock = $this->resumable_uploads->lock($upload);
    if (null === $lock) {
      // An earlier call is still checking the project; its client most likely gave up waiting and retried.
      $responseCode = Response::HTTP_CONFLICT;

      return null;
    }

    try {
      // Read again under the lock: the call that held it before may have finished this upload.
      $upload = $this->resumable_uploads->find($id, $user);
      if (!$upload instanceof ResumableUpload) {
        $responseCode = Response::HTTP_NOT_FOUND;

        return null;
      }

      if (null !== $upload->completed_project_id) {
        return $this->completedUploadResponse($upload->completed_project_id, $responseCode, $responseHeaders);
      }

      if (null !== $upload->failure_type) {
        $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;

        return ApiErrorResponse::createModel(Response::HTTP_UNPROCESSABLE_ENTITY, $upload->failure_type, (string) $upload->failure_message);
      }

      try {
        $file = $this->resumable_uploads->assemble($upload);
      } catch (ResumableUploadException $e) {
        // Missing chunks can still be sent, so the upload stays until it expires.
        $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;
        $this->logUploadOutcome('rejected', $e->getMessage(), null, $upload->flavor, $started, 'resumable');

        return ApiErrorResponse::createModel(Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage(), $this->resumableUploadErrorText($e));
      }

      // Joining, checking and extracting a file of several hundred MB can outlast the default limit.
      set_time_limit(300);
      $accept_language = $this->facade->getResponseManager()->sanitizeLocale(
        $this->request_stack->getCurrentRequest()?->headers->get('Accept-Language') ?? 'en'
      );

      $rejected = false;
      $response = $this->createProjectFromFile($user, $file, $accept_language, $upload->flavor, $upload->private, $upload->project_id, $started, 'resumable', $responseCode, $responseHeaders, $rejected);
      if ($response instanceof ProjectResponse && Response::HTTP_CREATED === $responseCode) {
        $this->resumable_uploads->markCompleted($upload, (string) $response->getId());
      } elseif ($rejected && ($error = $response instanceof ErrorResponse ? $response->getError() : null) instanceof ErrorResponseError) {
        $this->resumable_uploads->markFailed($upload, (string) $error->getType(), (string) $error->getMessage());
      } else {
        // A server-side fault, not a verdict on the file: keep the chunks so completing again needs no re-upload,
        // and say so with a 5xx instead of the single upload's 422.
        $this->resumable_uploads->discardAssembled($upload);
        $responseCode = Response::HTTP_INTERNAL_SERVER_ERROR;

        return ApiErrorResponse::createModel(Response::HTTP_INTERNAL_SERVER_ERROR, 'upload_failed', 'The server could not finish this upload. Complete it again later; the chunks are kept.');
      }

      return $response;
    } finally {
      $this->resumable_uploads->unlock($lock);
    }
  }

  /**
   * Shared by the single-request upload and the resumable upload's completion: the same checks, malware scan and
   * project creation either way. $rejected tells a verdict on the file apart from a server-side failure.
   */
  private function createProjectFromFile(User $user, File $file, string $accept_language, string $flavor, bool $private, ?string $project_id, float $started, string $via, int &$responseCode, array &$responseHeaders, bool &$rejected = false): ?object
  {
    // Needed (for tests) to make sure everything is up to date (followers, ..)
    $this->facade->getProcessor()->refreshUser($user);

    try {
      $project = $this->facade->getProcessor()->addProject(
        new AddProjectRequest(
          $user,
          $file,
          $this->facade->getLoader()->getClientIp(),
          $accept_language,
          $flavor,
          $project_id,
        )
      );
    } catch (InvalidCatrobatFileException $e) {
      $this->facade->getLogger()->warning('Project upload rejected: '.$e->getMessage(), [
        'debug' => $e->getDebugMessage(),
      ]);
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;
      $rejected = true;
      $this->logUploadOutcome('rejected', $e->getMessage(), $file, $flavor, $started, $via);
      $error_response = $this->facade->getResponseManager()->createUploadValidationErrorResponse($e->getMessage(), $accept_language);
      $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $error_response);
      $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

      return $error_response;
    } catch (\Exception $e) {
      $this->facade->getLogger()->critical('Project Upload broken: '.$e->getMessage().$e->getTraceAsString());
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;
      $this->logUploadOutcome('failed', $e::class, $file, $flavor, $started, $via);
      $error_response = $this->facade->getResponseManager()->createUploadErrorResponse($accept_language);
      $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $error_response);
      $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

      return $error_response;
    }

    if (!$project instanceof Project) {
      $responseCode = Response::HTTP_INTERNAL_SERVER_ERROR;
      $this->logUploadOutcome('failed', 'no project created', $file, $flavor, $started, $via);

      return null;
    }

    // Setting the project's attributes
    $project->setPrivate($private);
    $this->facade->getProcessor()->saveProject($project);

    // Since we have come this far, the project upload is completed
    $responseCode = Response::HTTP_CREATED;
    $this->logUploadOutcome('created', null, $file, $flavor, $started, $via);
    $responseHeaders['Location'] = $this->facade->getResponseManager()->createProjectLocation($project);

    return $this->facade->getResponseManager()->createProjectDataResponse($project, 'ALL');
  }

  /**
   * The answer to a repeated completion call: the project the first call created, unless it is gone since.
   */
  private function completedUploadResponse(string $project_id, int &$responseCode, array &$responseHeaders): ?ProjectResponse
  {
    $project = $this->facade->getLoader()->findProjectByID($project_id, true);
    if (!$project instanceof Project) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $responseCode = Response::HTTP_CREATED;
    $responseHeaders['Location'] = $this->facade->getResponseManager()->createProjectLocation($project);

    return $this->facade->getResponseManager()->createProjectDataResponse($project, 'ALL');
  }

  private function findOwnUpload(string $id, int &$responseCode, ?User &$user = null): ?ResumableUpload
  {
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    if (!$user instanceof User) {
      $responseCode = Response::HTTP_UNAUTHORIZED;

      return null;
    }

    $upload = $this->resumable_uploads->find($id, $user);
    if (!$upload instanceof ResumableUpload) {
      $responseCode = Response::HTTP_NOT_FOUND;
    }

    return $upload;
  }

  private function createUploadSessionResponse(ResumableUpload $upload): ProjectUploadSessionResponse
  {
    return new ProjectUploadSessionResponse([
      'id' => $upload->id,
      'file_size' => $upload->file_size,
      'chunk_size' => $upload->chunk_size,
      'total_chunks' => $upload->total_chunks,
      'received_chunks' => $this->resumable_uploads->receivedChunks($upload),
      'max_file_size' => $this->resumable_uploads->getMaxFileSize(),
      'expires_at' => \DateTime::createFromImmutable($upload->expires_at),
    ]);
  }

  private function resumableUploadErrorText(ResumableUploadException $e): string
  {
    return match ($e->getMessage()) {
      ResumableUploadException::TOO_LARGE => sprintf('The project file is larger than the %d MB this server accepts.', intdiv($this->resumable_uploads->getMaxFileSize(), 1_048_576)),
      ResumableUploadException::TOO_MANY_OPEN => 'Your other uploads are still being processed; try again in a minute.',
      ResumableUploadException::INCOMPLETE => 'Some chunks are missing; send them and complete the upload again.',
      ResumableUploadException::BAD_CHECKSUM => 'The uploaded file does not match its checksum.',
      default => 'The upload could not be accepted.',
    };
  }

  /**
   * Uploads that never reach PHP (over the nginx/Cloudflare body limit) only show up as 413 in the nginx log.
   */
  private function logUploadOutcome(string $outcome, ?string $reason, ?File $file, string $flavor, float $started, string $via = 'single'): void
  {
    $user_agent = (string) $this->request_stack->getCurrentRequest()?->headers->get('User-Agent');
    $this->uploadLogger->info('Project upload '.$outcome, [
      'outcome' => $outcome,
      'reason' => $reason,
      'via' => $via,
      'bytes' => $file?->getSize(),
      'flavor' => $flavor,
      'client' => str_contains($user_agent, 'Catrobat') || str_starts_with($user_agent, 'okhttp') ? 'app' : 'browser',
      'ms' => (int) round((microtime(true) - $started) * 1000),
    ]);
  }
}
