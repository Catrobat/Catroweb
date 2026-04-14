<?php

declare(strict_types=1);

namespace App\Api\Services\Projects;

use App\Api\Exceptions\ApiErrorResponse;
use App\Api\Services\Base\AbstractResponseManager;
use App\Api\Services\Base\TranslatorAwareTrait;
use App\Api\Traits\CursorPaginationTrait;
use App\Api\Traits\KeysetCursorTrait;
use App\DB\Entity\Project\Extension;
use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\ProjectCodeStatistics;
use App\DB\Entity\Project\Special\ExampleProgram;
use App\DB\Entity\Project\Special\FeaturedProgram;
use App\DB\Entity\Project\Special\SpecialProgram;
use App\DB\Entity\Project\Tag;
use App\Project\ProjectManager;
use App\Storage\ImageRepository;
use App\Storage\StorageLifecycleService;
use App\Utils\ElapsedTimeStringFormatter;
use Doctrine\ORM\EntityManagerInterface;
use OpenAPI\Server\Model\CodeStatisticsResponse;
use OpenAPI\Server\Model\ErrorResponse;
use OpenAPI\Server\Model\ExtensionResponse;
use OpenAPI\Server\Model\FeaturedProjectResponse;
use OpenAPI\Server\Model\FeaturedProjectsListResponse;
use OpenAPI\Server\Model\ProjectResponse;
use OpenAPI\Server\Model\ProjectsCategory;
use OpenAPI\Server\Model\ProjectsListResponse;
use OpenAPI\Server\Model\TagResponse;
use OpenAPI\Server\Service\SerializerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProjectsResponseManager extends AbstractResponseManager
{
  use CursorPaginationTrait;
  use KeysetCursorTrait;
  use TranslatorAwareTrait;

  public function __construct(
    private readonly ElapsedTimeStringFormatter $time_formatter,
    private readonly UrlGeneratorInterface $url_generator,
    private readonly ParameterBagInterface $parameter_bag,
    TranslatorInterface $translator,
    SerializerInterface $serializer,
    private readonly ProjectManager $project_manager,
    \Psr\Cache\CacheItemPoolInterface $cache,
    private readonly EntityManagerInterface $entity_manager,
    private readonly ImageRepository $image_repository,
  ) {
    parent::__construct($translator, $serializer, $cache);
  }

  /**
   * @param ?string $attributes Comma-separated list of attributes to include into response
   */
  public function createProjectDataResponse(Program|SpecialProgram $project, ?string $attributes): ProjectResponse
  {
    $default_attributes = ['id', 'name', 'author', 'views', 'downloads', 'flavor', 'uploaded_string', 'screenshot', 'project_url'];
    $catroid_catty_hotfixes = ['tags', 'description', 'version', 'uploaded_at', 'download_url', 'filesize', 'not_for_kids'];

    if (null === $attributes || '' === $attributes || '0' === $attributes) {
      $attributes_list = array_merge($default_attributes, $catroid_catty_hotfixes);
    } elseif ('ALL' === $attributes) {
      $attributes_list = ['id', 'name', 'author', 'author_id', 'scratch_id', 'description', 'credits', 'version', 'views', 'downloads', 'reactions', 'comments', 'private', 'flavor', 'tags', 'extensions', 'uploaded_at', 'uploaded_string', 'screenshot', 'project_url', 'download_url', 'filesize', 'not_for_kids', 'retention_days', 'retention_expiry'];
    } else {
      $attributes_list = explode(',', $attributes);
    }

    /** @var Program $extraced_project */
    $extraced_project = $project->isExample() ? $project->getProgram() : $project;

    $data = [];

    if (in_array('id', $attributes_list, true)) {
      $data['id'] = $extraced_project->getId();
    }

    if (in_array('name', $attributes_list, true)) {
      $data['name'] = $extraced_project->getName();
    }

    if (in_array('author', $attributes_list, true)) {
      $data['author'] = $extraced_project->getUser()->getUserIdentifier();
    }

    if (in_array('author_id', $attributes_list, true)) {
      $data['author_id'] = $extraced_project->getUser()?->getId();
    }

    if (in_array('scratch_id', $attributes_list, true)) {
      $data['scratch_id'] = $extraced_project->getScratchId();
    }

    if (in_array('description', $attributes_list, true)) {
      $data['description'] = $extraced_project->getDescription() ?? '';
    }

    if (in_array('credits', $attributes_list, true)) {
      $data['credits'] = $extraced_project->getCredits() ?? '';
    }

    if (in_array('version', $attributes_list, true)) {
      $data['version'] = $extraced_project->getCatrobatVersionName();
    }

    if (in_array('views', $attributes_list, true)) {
      $data['views'] = $extraced_project->getViews();
    }

    if (in_array('downloads', $attributes_list, true)) {
      $data['downloads'] = $extraced_project->getDownloads();
    }

    if (in_array('reactions', $attributes_list, true)) {
      $data['reactions'] = $extraced_project->getLikes()->count();
    }

    if (in_array('comments', $attributes_list, true)) {
      $data['comments'] = $extraced_project->getComments()->count();
    }

    if (in_array('private', $attributes_list, true)) {
      $data['private'] = $extraced_project->getPrivate();
    }

    if (in_array('flavor', $attributes_list, true)) {
      $data['flavor'] = $extraced_project->getFlavor() ?? '';
    }

    if (in_array('tags', $attributes_list, true)) {
      $tags = [];
      $project_tags = $extraced_project->getTags();
      /** @var Tag $tag */
      foreach ($project_tags as $tag) {
        $tags[$tag->getInternalTitle()] = $this->trans($tag->getTitleLtmCode());
      }

      $data['tags'] = $tags;
    }

    if (in_array('extensions', $attributes_list, true)) {
      $extensions = [];
      $project_extensions = $extraced_project->getExtensions();
      /** @var Extension $extension */
      foreach ($project_extensions as $extension) {
        $extensions[$extension->getInternalTitle()] = $this->trans($extension->getTitleLtmCode());
      }

      $data['extensions'] = $extensions;
    }

    if (in_array('uploaded_at', $attributes_list, true)) {
      $data['uploaded_at'] = \DateTime::createFromInterface($extraced_project->getUploadedAt());
    }

    if (in_array('uploaded_string', $attributes_list, true)) {
      try {
        $data['uploaded_string'] = $this->time_formatter->format($extraced_project->getUploadedAt()->getTimestamp());
      } catch (\Exception) {
        $data['uploaded_string'] = $extraced_project->getUploadedAt()->format(\DateTimeInterface::RFC2822);
      }
    }

    if (in_array('screenshot', $attributes_list, true)) {
      $project_id = $extraced_project->getId();
      $data['screenshot'] = (!$project->isExample() && null !== $project_id)
        ? $this->project_manager->getScreenshotVariants($project_id)
        : null;
    }

    if (in_array('project_url', $attributes_list, true)) {
      $data['project_url'] = ltrim($this->createProjectLocation($project->getProgram()), '/');
    }

    if (in_array('download_url', $attributes_list, true)) {
      $data['download_url'] = ltrim($this->url_generator->generate(
        'open_api_server_projects_projectsidcatrobatget',
        [
          'id' => $extraced_project->getId(),
        ],
        UrlGeneratorInterface::ABSOLUTE_URL
      ), '/');
    }

    if (in_array('filesize', $attributes_list, true)) {
      $data['filesize'] = ($extraced_project->getFilesize() / 1_048_576);
    }

    if (in_array('not_for_kids', $attributes_list, true)) {
      $data['not_for_kids'] = $project->getNotForKids();
    }

    if (in_array('retention_days', $attributes_list, true) || in_array('retention_expiry', $attributes_list, true)) {
      $retention_days = $this->computeRetentionDays($extraced_project);
      if (in_array('retention_days', $attributes_list, true)) {
        $data['retention_days'] = $retention_days;
      }
      if (in_array('retention_expiry', $attributes_list, true)) {
        if (StorageLifecycleService::PROTECTED_DAYS === $retention_days) {
          $data['retention_expiry'] = null;
        } else {
          $uploaded = $extraced_project->getUploadedAt();
          $data['retention_expiry'] = \DateTime::createFromInterface($uploaded)->modify("+{$retention_days} days");
        }
      }
    }

    return new ProjectResponse($data);
  }

  public function createProjectsListResponse(array $projects, int $limit, ?string $attributes = null, int $offset = 0): ProjectsListResponse
  {
    $has_more = count($projects) > $limit;
    if ($has_more) {
      array_pop($projects);
    }

    $data = [];
    foreach ($projects as $project) {
      $data[] = $this->createProjectDataResponse($project, $attributes);
    }

    $next_cursor = ($has_more && [] !== $data) ? $this->encodeCursorFromOffset($offset, count($data)) : null;

    return new ProjectsListResponse([
      'data' => $data,
      'next_cursor' => $next_cursor,
      'has_more' => $has_more,
    ]);
  }

  /**
   * Create a paginated projects list response using keyset cursor from actual project data.
   *
   * @param string $sort_by The sort column: 'uploaded_at', 'views', or 'downloads'
   */
  public function createProjectsKeysetResponse(array $projects, int $limit, string $sort_by, ?string $attributes = null): ProjectsListResponse
  {
    $has_more = count($projects) > $limit;
    if ($has_more) {
      array_pop($projects);
    }

    $data = [];
    foreach ($projects as $project) {
      $data[] = $this->createProjectDataResponse($project, $attributes);
    }

    $next_cursor = null;
    if ($has_more && [] !== $data) {
      /** @var Program $last */
      $last = end($projects);
      $last_id = (string) $last->getId();
      $next_cursor = match ($sort_by) {
        'views' => $this->encodeIntKeysetCursor($last->getViews(), $last_id),
        'downloads' => $this->encodeIntKeysetCursor($last->getDownloads(), $last_id),
        default => $this->encodeDateKeysetCursor($last->getUploadedAt(), $last_id),
      };
    }

    return new ProjectsListResponse([
      'data' => $data,
      'next_cursor' => $next_cursor,
      'has_more' => $has_more,
    ]);
  }

  /**
   * Create a paginated featured projects list response using keyset cursor.
   */
  public function createFeaturedProjectsKeysetResponse(array $featured_projects, int $limit, ?string $attributes = null): FeaturedProjectsListResponse
  {
    $has_more = count($featured_projects) > $limit;
    if ($has_more) {
      array_pop($featured_projects);
    }

    $data = [];
    /** @var FeaturedProgram $featured_project */
    foreach ($featured_projects as $featured_project) {
      $data[] = $this->createFeaturedProjectResponse($featured_project, $attributes);
    }

    $next_cursor = null;
    if ($has_more && [] !== $data) {
      /** @var FeaturedProgram $last */
      $last = end($featured_projects);
      $next_cursor = $this->encodeIntKeysetCursor($last->getPriority(), (string) $last->getId());
    }

    return new FeaturedProjectsListResponse([
      'data' => $data,
      'next_cursor' => $next_cursor,
      'has_more' => $has_more,
    ]);
  }

  /**
   * @deprecated Use createProjectsListResponse() for paginated endpoints
   */
  public function createProjectsDataResponse(array $projects, ?string $attributes = null): array
  {
    $response = [];
    foreach ($projects as $project) {
      $response[] = $this->createProjectDataResponse($project, $attributes);
    }

    return $response;
  }

  private function computeRetentionDays(Program $project): int
  {
    if ($project->isStorageProtected()) {
      return StorageLifecycleService::PROTECTED_DAYS;
    }

    $projectId = $project->getId();
    if (null !== $projectId) {
      $featured = (int) $this->entity_manager->createQueryBuilder()
        ->select('COUNT(f.id)')
        ->from(FeaturedProgram::class, 'f')
        ->where('f.program = :project')
        ->setParameter('project', $project)
        ->getQuery()
        ->getSingleScalarResult()
      ;
      if ($featured > 0) {
        return StorageLifecycleService::PROTECTED_DAYS;
      }

      $example = (int) $this->entity_manager->createQueryBuilder()
        ->select('COUNT(e.id)')
        ->from(ExampleProgram::class, 'e')
        ->where('e.program = :project')
        ->setParameter('project', $project)
        ->getQuery()
        ->getSingleScalarResult()
      ;
      if ($example > 0) {
        return StorageLifecycleService::PROTECTED_DAYS;
      }
    }

    if ($project->getDownloads() >= 10) {
      return StorageLifecycleService::ACTIVE_DAYS;
    }

    $user = $project->getUser();
    if (null !== $user) {
      $lastLogin = $user->getLastLogin();
      if (null !== $lastLogin && $lastLogin > new \DateTime('-180 days')) {
        return StorageLifecycleService::ACTIVE_DAYS;
      }
    }

    if ($project->isVisible() && !$project->getAutoHidden() && null !== $user && $user->isVerified()) {
      return StorageLifecycleService::STANDARD_DAYS;
    }

    return StorageLifecycleService::SHORT_DAYS;
  }

  public function createFeaturedProjectResponse(FeaturedProgram $featured_project, ?string $attributes = null): FeaturedProjectResponse
  {
    if (null === $attributes || '' === $attributes || '0' === $attributes || 'ALL' === $attributes) {
      $attributes_list = ['id', 'project_id', 'project_url', 'url', 'name', 'author', 'featured_image'];
    } else {
      $attributes_list = explode(',', $attributes);
    }

    $data = [];
    $program = $featured_project->getProgram();

    if (in_array('id', $attributes_list, true)) {
      $data['id'] = $featured_project->getId() ?? -1;
    }

    if (in_array('project_id', $attributes_list, true)) {
      $data['project_id'] = $program?->getId() ?? '';
    }

    if (in_array('name', $attributes_list, true)) {
      $data['name'] = $program?->getName() ?? '';
    }

    if (in_array('author', $attributes_list, true)) {
      $data['author'] = $program?->getUser()?->getUserIdentifier() ?? '';
    }

    if (in_array('featured_image', $attributes_list, true)) {
      $data['featured_image'] = $this->image_repository->getFeaturedVariants($featured_project->getId());
    }

    if (in_array('url', $attributes_list, true) || in_array('project_url', $attributes_list, true)) {
      $url = $featured_project->getUrl();
      $project_url = null;
      if (null === $url || '' === $url || '0' === $url) {
        $url = ltrim($this->createProjectLocation($featured_project->getProgram()), '/');
        $project_url = $url;
      }

      if (in_array('project_url', $attributes_list, true)) {
        $data['project_url'] = $project_url;
      }

      if (in_array('url', $attributes_list, true)) {
        $data['url'] = $url;
      }
    }

    return new FeaturedProjectResponse($data);
  }

  public function createFeaturedProjectsListResponse(array $featured_projects, int $limit, ?string $attributes = null, int $offset = 0): FeaturedProjectsListResponse
  {
    $has_more = count($featured_projects) > $limit;
    if ($has_more) {
      array_pop($featured_projects);
    }

    $data = [];

    /** @var FeaturedProgram $featured_project */
    foreach ($featured_projects as $featured_project) {
      $data[] = $this->createFeaturedProjectResponse($featured_project, $attributes);
    }

    $next_cursor = ($has_more && [] !== $data) ? $this->encodeCursorFromOffset($offset, count($data)) : null;

    return new FeaturedProjectsListResponse([
      'data' => $data,
      'next_cursor' => $next_cursor,
      'has_more' => $has_more,
    ]);
  }

  public function createProjectCategoryResponse(array $projects, string $category, string $locale, ?string $attributes = null): ProjectsCategory
  {
    return new ProjectsCategory([
      'projects_list' => $this->createProjectsDataResponse($projects, $attributes),
      'type' => $category,
      'name' => $this->__('category.'.$category, [], $locale),
    ]);
  }

  public function createProjectLocation(Program $project): string
  {
    return $this->url_generator->generate(
      'program',
      [
        'theme' => $this->parameter_bag->get('umbrellaTheme'),
        'id' => $project->getId(),
      ],
      UrlGeneratorInterface::ABSOLUTE_URL
    );
  }

  public function createUploadErrorResponse(string $locale): ErrorResponse
  {
    return ApiErrorResponse::createModel(
      422,
      'validation_error',
      $this->__('api.projectsPost.creating_error', [], $locale),
    );
  }

  public function createUploadValidationErrorResponse(string $translation_key, string $locale): ErrorResponse
  {
    return ApiErrorResponse::createModel(
      422,
      'validation_error',
      $this->__($translation_key, [], $locale),
    );
  }

  public function createProjectsExtensionsResponse(array $extensions, string $locale): array
  {
    $response = [];

    /** @var Extension $extension */
    foreach ($extensions as $extension) {
      $response[] = $this->createExtensionResponse($extension, $locale);
    }

    return $response;
  }

  public function createExtensionResponse(Extension $extension, string $locale): ExtensionResponse
  {
    return new ExtensionResponse([
      'id' => $extension->getInternalTitle(),
      'text' => $this->__($extension->getTitleLtmCode(), [], $locale),
    ]);
  }

  public function createProjectsTagsResponse(array $tags, string $locale): array
  {
    $response = [];
    /** @var Tag $tag */
    foreach ($tags as $tag) {
      $response[] = $this->createTagResponse($tag, $locale);
    }

    return $response;
  }

  public function createTagResponse(Tag $tag, string $locale): TagResponse
  {
    return new TagResponse([
      'id' => $tag->getInternalTitle(),
      'text' => $this->__($tag->getTitleLtmCode(), [], $locale),
    ]);
  }

  public function createProjectCatrobatFileResponse(string $id, File $file): BinaryFileResponse
  {
    $response = new BinaryFileResponse($file);
    $response->headers->set(
      'Content-Disposition',
      'attachment; filename="'.$id.'.catrobat"'
    );
    $response->headers->set('Content-Type', 'application/zip');

    return $response;
  }

  public function createCodeStatisticsResponse(ProjectCodeStatistics $stats): CodeStatisticsResponse
  {
    return new CodeStatisticsResponse([
      'score_abstraction' => $stats->getScoreAbstraction(),
      'score_parallelism' => $stats->getScoreParallelism(),
      'score_synchronization' => $stats->getScoreSynchronization(),
      'score_logical_thinking' => $stats->getScoreLogicalThinking(),
      'score_flow_control' => $stats->getScoreFlowControl(),
      'score_user_interactivity' => $stats->getScoreUserInteractivity(),
      'score_data_representation' => $stats->getScoreDataRepresentation(),
      'score_bonus' => $stats->getScoreBonus(),
      'score_total' => $stats->getScoreTotal(),
      'scoring_version' => $stats->getScoringVersion(),
    ]);
  }

  public function createUpdateFailureResponse(int $failure, string $locale): ErrorResponse
  {
    if (ProjectsApiProcessor::SERVER_ERROR_SAVE_XML === $failure) {
      return ApiErrorResponse::createModel(
        500,
        'internal_error',
        $this->__('api.updateProject.xmlError', [], $locale),
      );
    }

    if (ProjectsApiProcessor::SERVER_ERROR_SCREENSHOT === $failure) {
      return ApiErrorResponse::createModel(
        500,
        'internal_error',
        $this->__('api.updateProject.screenshotError', [], $locale),
      );
    }

    return ApiErrorResponse::createModel(
      500,
      'internal_error',
      'An unexpected error occurred',
    );
  }
}
